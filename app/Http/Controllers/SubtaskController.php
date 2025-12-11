<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Subtask;
use App\Models\Kanban;
use App\Models\KanbanFile;
use App\Models\KanbanLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SubtaskController extends Controller
{
    /* ============================================================
        UPLOAD FILE
    ============================================================ */
    private function uploadFiles(Request $request, $projectId, $kanbanId, $subtaskId)
    {
        if (!$request->hasFile('files')) return;

        foreach ($request->file('files') as $file) {

            $stored = $file->store('kanban/files', 'public');

            $fileModel = KanbanFile::create([
                'id'         => Str::uuid(),
                'kanbanId'   => $kanbanId,
                'subtaskId'  => $subtaskId,
                'uploadedBy' => Auth::id(),
                'filename'   => $file->getClientOriginalName(),
                'file_path'  => $stored,
                'file_type'  => $file->getClientMimeType(),
                'file_size'  => $file->getSize(),
            ]);

            KanbanLog::createLog([
                'projectId'   => $projectId,
                'kanbanId'    => $kanbanId,
                'subtaskId'   => $subtaskId,
                'action'      => 'CREATE',
                'entity_type' => 'SUBTASK_FILE',
                'description' => "Uploaded file '{$fileModel->filename}'",
                'new_values'  => $fileModel->only([
                    'id','kanbanId','subtaskId','filename','file_path','file_size','file_type'
                ]),
            ]);
        }
    }

    /* ============================================================
        GET SUBTASKS
    ============================================================ */
    public function index($projectId, $kanbanId)
    {
        $subtasks = Subtask::where('kanbanId', $kanbanId)
            ->whereNull('deleted_at')
            ->with(['files' => fn($q) => $q->whereNull('deleted_at')])
            ->orderBy('priority')
            ->get();

        return response()->json([
            'success' => true,
            'subtasks' => $subtasks
        ]);
    }

    /* ============================================================
        CREATE SUBTASK
    ============================================================ */
    public function store(Request $request, $projectId, $kanbanId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        $subtask = Subtask::create([
            'id'         => Str::uuid(),
            'kanbanId'   => $kanbanId,
            'title'      => $request->title,
            'description'=> $request->description,
            'notes'      => $request->notes,
            'priority'   => $request->priority,
            'status'     => $request->status ?? 'todo',
            'date_start' => $request->date_start,
            'date_end'   => $request->date_end,
            'duration'   => ($request->date_start && $request->date_end)
                ? Carbon::parse($request->date_start)->diffInDays($request->date_end)
                : null,
        ]);

        $this->uploadFiles($request, $projectId, $kanbanId, $subtask->id);

        KanbanLog::createLog([
            'projectId'   => $projectId,
            'kanbanId'    => $kanbanId,
            'subtaskId'   => $subtask->id,
            'action'      => 'CREATE',
            'entity_type' => 'SUBTASK',
            'description' => "Created subtask '{$subtask->title}'",
            'new_values'  => $subtask->only([
                'id','kanbanId','title','description','notes','priority','status','date_start','date_end','duration'
            ]),
        ]);

        $subtask->load('files');

        return response()->json(['success' => true, 'subtask' => $subtask]);
    }

    /* ============================================================
        UPDATE SUBTASK
    ============================================================ */
    public function update(Request $request, $projectId, $kanbanId, $subtaskId)
    {
        $subtask = Subtask::where('kanbanId', $kanbanId)
            ->where('id', $subtaskId)
            ->firstOrFail();

        $old = $subtask->only([
            'id','kanbanId','title','description','notes','priority','status',
            'date_start','date_end','duration'
        ]);

        $subtask->update([
            'title'       => $request->title,
            'description' => $request->description,
            'notes'       => $request->notes,
            'priority'    => $request->priority,
            'status'      => $request->status,
            'date_start'  => $request->date_start,
            'date_end'    => $request->date_end,
            'duration'    => ($request->date_start && $request->date_end)
                ? Carbon::parse($request->date_start)->diffInDays($request->date_end)
                : null,
        ]);

        $this->uploadFiles($request, $projectId, $kanbanId, $subtask->id);

        KanbanLog::createLog([
            'projectId'   => $projectId,
            'kanbanId'    => $kanbanId,
            'subtaskId'   => $subtask->id,
            'action'      => 'UPDATE',
            'entity_type' => 'SUBTASK',
            'description' => "Updated subtask '{$subtask->title}'",
            'old_values'  => $old,
            'new_values'  => $subtask->fresh()->only([
                'id','kanbanId','title','description','notes','priority','status','date_start','date_end','duration'
            ]),
        ]);

        return response()->json(['success' => true]);
    }

    /* ============================================================
        DELETE SUBTASK
    ============================================================ */
    public function destroy($projectId, $kanbanId, $subtaskId)
    {
        $subtask = Subtask::where('kanbanId', $kanbanId)
            ->where('id', $subtaskId)
            ->firstOrFail();

        KanbanLog::createLog([
            'projectId'   => $projectId,
            'kanbanId'    => $kanbanId,
            'subtaskId'   => $subtask->id,
            'action'      => 'DELETE',
            'entity_type' => 'SUBTASK',
            'description' => "Deleted subtask '{$subtask->title}'",
            'old_values'  => $subtask->only([
                'id','kanbanId','title','description','notes','priority','status','date_start','date_end','duration'
            ]),
        ]);

        $subtask->files()->delete();
        $subtask->delete();

        return response()->json(['success' => true]);
    }

    /* ============================================================
        DELETE SUBTASK FILE
    ============================================================ */
    public function deleteFile($projectId, $kanbanId, $fileId)
    {
        $file = KanbanFile::findOrFail($fileId);

        if (!$file->subtaskId) {
            return response()->json(['success' => false, 'message' => 'File bukan milik subtask'], 404);
        }

        $subtask = Subtask::find($file->subtaskId);

        if ($subtask->kanbanId != $kanbanId) {
            return response()->json(['success' => false, 'message' => 'File tidak sesuai kanban'], 403);
        }

        KanbanLog::createLog([
            'projectId'   => $projectId,
            'kanbanId'    => $kanbanId,
            'subtaskId'   => $subtask->id,
            'action'      => 'DELETE',
            'entity_type' => 'SUBTASK_FILE',
            'description' => "Deleted file '{$file->filename}'",
            'old_values'  => $file->only(['id','kanbanId','subtaskId','filename']),
        ]);

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return response()->json(['success' => true]);
    }

    /* ============================================================
        TOGGLE STATUS
    ============================================================ */
    public function toggleStatus(Request $request, $projectId, $kanbanId, $subtaskId)
    {
        $subtask = Subtask::where('kanbanId', $kanbanId)
            ->where('id', $subtaskId)
            ->firstOrFail();

        $old = $subtask->status;

        $subtask->update(['status' => $request->status]);

        KanbanLog::createLog([
            'projectId'   => $projectId,
            'kanbanId'    => $kanbanId,
            'subtaskId'   => $subtask->id,
            'action'      => 'STATUS',
            'entity_type' => 'SUBTASK',
            'description' => "Changed status from {$old} → {$request->status}",
            'old_values'  => ['status' => $old],
            'new_values'  => ['status' => $request->status],
        ]);

        return response()->json(['success' => true]);
    }
}
