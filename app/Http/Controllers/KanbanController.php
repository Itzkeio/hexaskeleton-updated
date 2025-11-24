<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Kanban;
use App\Models\Subtask;
use App\Models\Projects;
use Carbon\Carbon;
use App\Services\RbacService;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    protected $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    public function index($projectId)
    {
        // Cek akses RBAC
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.projects');

        // ambil project + semua relasi kanban langsung dengan subtasks
        $project = Projects::with(['kanban.subtasks'])->findOrFail($projectId);

        return view('kanban.index', compact('project'));
    }

    public function store(Request $request, $projectId)
    {
        // Cek akses RBAC
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.projects');

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
        ]);

        // Hitung duration otomatis
        $duration = null;

        if ($request->date_start && $request->date_end) {
            $duration = Carbon::parse($request->date_start)
                ->diffInDays(Carbon::parse($request->date_end));
        }

        Kanban::create([
            'id'         => Str::uuid(),
            'projectId'  => $projectId,
            'title'      => $request->title,
            'date_start' => $request->date_start,
            'date_end'   => $request->date_end,
            'duration'   => $duration,
            'picId'      => $request->picId,
            'description' => $request->description,
            'priority'   => $request->priority,
            'status'     => 'todo',
        ]);

        return redirect()->back()->with('success', 'Task berhasil ditambahkan.');
    }

    public function update(Request $request, $projectId, $id)
    {
        // Cek akses RBAC
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.projects');

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
        ]);

        $task = Kanban::where('projectId', $projectId)
            ->where('id', $id)
            ->firstOrFail();

        // Hitung duration otomatis
        $duration = null;
        if ($request->date_start && $request->date_end) {
            $duration = Carbon::parse($request->date_start)
                ->diffInDays(Carbon::parse($request->date_end));
        }

        $task->update([
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority,
            'date_start'  => $request->date_start,
            'date_end'    => $request->date_end,
            'duration'    => $duration,
        ]);

        // Return JSON untuk AJAX request
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Task berhasil diperbarui.',
                'task' => $task->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Task berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $projectId)
    {
        // Cek akses RBAC
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.projects');

        $request->validate([
            'id' => 'required',
            'status' => 'required|in:todo,inprogress,finished',
        ]);

        $updated = Kanban::where('id', $request->id)
            ->where('projectId', $projectId)
            ->update([
                'status' => $request->status
            ]);

        if ($updated) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Task tidak ditemukan'], 404);
        }
    }

    public function delete($projectId, $id)
    {
        // Cek akses RBAC
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.projects');

        $deleted = Kanban::where('id', $id)
            ->where('projectId', $projectId)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', 'Task berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Task tidak ditemukan.');
        }
    }

    // This method is actually handled by SubtaskController now
    // But we keep it for backward compatibility
    public function getSubtasks($project, $kanban)
    {
        // Cek akses RBAC
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.projects');

        $kanbanTask = Kanban::with('subtasks')->where('id', $kanban)->first();

        if (!$kanbanTask) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        return response()->json([
            'success' => true,
            'subtasks' => $kanbanTask->subtasks
        ]);
    }
}
