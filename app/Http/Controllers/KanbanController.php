<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Kanban;
use App\Models\Projects;
use App\Models\KanbanFile;
use App\Models\KanbanLog;
use Carbon\Carbon;
use App\Services\RbacService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{

    // protected $rbacService;

    // public function __construct(RbacService $rbacService)
    // {
    //     $this->rbacService = $rbacService;
    // }

    /* ============================================================
        KANBAN INDEX
    ============================================================ */
    public function index(Request $request, Projects $project)
    {
        //  $userId = Auth::user()->id;
        // $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.kanban');

        // if (!$hasAccess) {
        //     return view('access-denied');
        // }
        $project->load([
            'kanban' => function ($query) {
                $query->with([
                    'subtasks' => fn($q) => $q->whereNull('deleted_at'),
                    'files'    => fn($q) => $q->whereNull('deleted_at'),
                ])->whereNull('deleted_at');
            }
        ]);

        if ($request->ajax()) {
            return view('project-mgt.kanban.index', compact('project'));
        }

        return view('project-mgt.kanban.index', compact('project'));
    }

    /* ============================================================
        FILE UPLOADER (FIXED)
    ============================================================ */
    private function uploadFiles(Request $request, $projectId, $kanbanId = null, $subtaskId = null)
    {
        // $userId = Auth::user()->id;

        // // RBAC: cek akses membuat timeline
        // if (!$this->rbacService->userHasKeyAccess($userId, 'kanban.uploadFiles')) {
        //     return $this->denyAccess($request);
        // }

        if (!$request->hasFile('files')) return;

        foreach ($request->file('files') as $file) {

            $stored = $file->store('kanban/files', 'public');

            $fileModel = KanbanFile::create([
                'id'         => Str::uuid(),
                'kanbanId'   => $subtaskId ? null : $kanbanId,
                'subtaskId'  => $subtaskId ?? null,
                'uploadedBy' => Auth::id(),
                'filename'   => $file->getClientOriginalName(),
                'file_path'  => $stored,
                'file_type'  => $file->getClientMimeType(),
                'file_size'  => $file->getSize(),
                'description' => $request->file_description ?? null,
                
            ]);

            // LOG ← FIXED projectId
            KanbanLog::createLog([
                'projectId'   => $projectId,
                'kanbanId'    => $kanbanId,
                'subtaskId'   => $subtaskId,
                'action'      => 'CREATE',
                'entity_type' => $subtaskId ? 'SUBTASK_FILE' : 'KANBAN_FILE',
                'description' => "Uploaded file '{$fileModel->filename}'",
                'new_values'  => $fileModel->toArray(),
            ]);
        }
    }

    /* ============================================================
        CREATE TASK
    ============================================================ */
    public function store(Request $request, Projects $project)
    {
        $userId = Auth::user()->id;

        // // RBAC: cek akses membuat timeline
        // if (!$this->rbacService->userHasKeyAccess($userId, 'kanban.create')) {
        //     return $this->denyAccess($request);
        // }

        $request->validate([
            'title' => 'required',
            'priority' => 'required|in:low,normal,high,urgent',
            'files.*' => 'nullable|file|max:10240',
        ]);

        $duration = ($request->date_start && $request->date_end)
            ? Carbon::parse($request->date_start)->diffInDays($request->date_end)
            : null;

        $task = Kanban::create([
            'id'         => Str::uuid(),
            'projectId'  => $project->id,
            'title'      => $request->title,
            'description' => $request->description,
            'notes'      => $request->notes,
            'priority'   => $request->priority,
            'picType' => $project->picType,
            'picId'   => $this->resolvePicId($project),
            'date_start' => $request->date_start,
            'date_end'   => $request->date_end,
            'duration'   => $duration,
            'status' => $request->status ?? 'todo',
        ]);

        $this->uploadFiles($request, $project->id, $task->id);

        KanbanLog::createLog([
            'projectId'   => $project->id,
            'kanbanId'    => $task->id,
            'action'      => 'CREATE',
            'entity_type' => 'KANBAN',
            'description' => "Created task '{$task->title}'",
            'new_values'  => $task->toArray(),
        ]);

        // 🔥 Jika request AJAX → kirim JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'task'    => $task,
            ]);
        }

        // 🔥 Jika bukan AJAX → fallback redirect
        return redirect()->back()->with('success', 'Task berhasil ditambahkan.');
    }


    /* ============================================================
        UPDATE TASK
    ============================================================ */
    public function update(Request $request, Projects $project, Kanban $kanban)
    {
        // $userId = Auth::user()->id;

        // // RBAC: cek akses membuat timeline
        // if (!$this->rbacService->userHasKeyAccess($userId, 'kanban.update')) {
        //     return $this->denyAccess($request);
        // }

        if ($kanban->projectId !== $project->id) {
            return response()->json(['success' => false], 403);
        }

        $request->validate([
            'title' => 'required',
            'priority' => 'required|in:low,normal,high,urgent',
            'files.*' => 'nullable|file|max:10240',
        ]);

        $old = $kanban->toArray();

        $duration = ($request->date_start && $request->date_end)
            ? Carbon::parse($request->date_start)->diffInDays($request->date_end)
            : null;

        $kanban->update([
            'title'       => $request->title,
            'description' => $request->description,
            'notes'       => $request->notes,
            'priority'    => $request->priority,
            'picType' => $project->picType,
            'picId'   => $this->resolvePicId($project),
            'date_start'  => $request->date_start,
            'date_end'    => $request->date_end,
            'duration'    => $duration,
        ]);

        $this->uploadFiles($request, $project->id, $kanban->id, null);

        KanbanLog::createLog([
            'projectId'   => $project->id,
            'kanbanId'    => $kanban->id,
            'action'      => 'UPDATE',
            'entity_type' => 'KANBAN',
            'description' => "Updated task '{$kanban->title}'",
            'old_values'  => $old,
            'new_values'  => $kanban->fresh()->toArray(),
        ]);

        return response()->json(['success' => true]);
    }

    /* ============================================================
        DELETE TASK
    ============================================================ */
    public function destroy(Projects $project, Kanban $kanban)
    {

        if ($kanban->projectId !== $project->id) {
            return response()->json(['success' => false], 403);
        }

        KanbanLog::createLog([
            'projectId'   => $project->id,
            'kanbanId'    => $kanban->id,
            'action'      => 'DELETE',
            'entity_type' => 'KANBAN',
            'description' => "Deleted task '{$kanban->title}'",
            'old_values'  => $kanban->toArray(),
        ]);

        $kanban->subtasks()->delete();
        $kanban->files()->delete();
        $kanban->delete();

        return response()->json(['success' => true]);
    }

    /* ============================================================
        DELETE FILE
    ============================================================ */
    public function destroyFile(Projects $project, KanbanFile $file)
    {
        // Cek ownership via relasi project->kanban (tanpa tergantung camel/snake)
        $isOwnedByProject = false;

        // Jika file terkait langsung ke kanban
        if ($file->kanbanId) {
            $isOwnedByProject = $project->kanban()->where('id', $file->kanbanId)->exists();
        }

        // Jika file terkait ke subtask -> cek subtask ada di salah satu kanban project ini
        if (!$isOwnedByProject && $file->subtaskId) {
            $isOwnedByProject = $project->kanban()
                ->whereHas('subtasks', function ($q) use ($file) {
                    $q->where('id', $file->subtaskId);
                })->exists();
        }

        if (!$isOwnedByProject) {
            return response()->json(['success' => false, 'message' => 'File tidak milik project ini'], 403);
        }

        // Log
        KanbanLog::createLog([
            'projectId'   => $project->id,
            'kanbanId'    => $file->kanbanId,
            'subtaskId'   => $file->subtaskId,
            'action'      => 'DELETE',
            'entity_type' => $file->subtaskId ? 'SUBTASK_FILE' : 'KANBAN_FILE',
            'description' => "Deleted file '{$file->filename}'",
            'old_values'  => $file->toArray(),
        ]);

        // Hapus file storage jika ada
        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        return response()->json(['success' => true]);
    }


    /* ============================================================
        MOVE TASK
    ============================================================ */
    public function updateStatus(Request $request, Projects $project)
    {
        $userId = Auth::user()->id;

        // // RBAC: cek akses membuat timeline
        // if (!$this->rbacService->userHasKeyAccess($userId, 'kanban.updateStatus')) {
        //     return $this->denyAccess($request);
        // }

        $request->validate([
            'id' => 'required',
            'status' => 'required|string',
        ]);

        // 🔍 Cek apakah status benar-benar milik project ini
        $status = \App\Models\KanbanStatus::where('projectId', $project->id)
            ->where('key', $request->status)
            ->first();

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status for this project'
            ], 422);
        }

        // 🔍 Ambil task
        $task = Kanban::where('id', $request->id)
            ->where('projectId', $project->id)
            ->firstOrFail();

        $oldStatus = $task->status;

        // 🔄 Update task ke status baru
        $task->update([
            'status' => $status->key,
        ]);

        // 📝 Log
        KanbanLog::createLog([
            'projectId'   => $project->id,
            'kanbanId'    => $task->id,
            'action'      => 'MOVE',
            'entity_type' => 'KANBAN',
            'description' => "Moved task '{$task->title}' from {$oldStatus} to {$status->key}",
            'old_values'  => ['status' => $oldStatus],
            'new_values'  => ['status' => $status->key],
        ]);

        // 🎨 Kembalikan data warna untuk JS (agar badge berubah)
        return response()->json([
            'success' => true,
            'status' => [
                'key'          => $status->key,
                'label'        => $status->label,
                'color_bg'     => $status->color_bg,
                'color_border' => $status->color_border,
            ]
        ]);
    }

    private function resolvePicId(Projects $project)
{
    // Individual PIC → langsung return user ID
    if ($project->picType === 'individual') {
        return $project->picId;
    }

    // Group PIC → ambil anggota group
    if ($project->picType === 'group') {

        $members = \App\Models\GroupMember::where('group_id', $project->picId)
            ->pluck('user_id');

        if ($members->isEmpty()) {
            // Jika group tidak punya anggota → fallback ke user login
            return auth()->id();
        }

        // Gunakan anggota pertama sebagai PIC representative
        return $members->first();
    }

    return auth()->id(); // fallback aman
}
}
