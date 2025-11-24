<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Subtask;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\RbacService;

class SubtaskController extends Controller
{
    protected $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    /** GET ALL SUBTASKS (AJAX) */
    public function index($project, $kanban)
    {
        // Cek akses RBAC
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.projects');

        if (!$hasAccess) {
            return view('access-denied');
        }
        $subtasks = Subtask::where('kanbanId', $kanban)
            ->orderBy('priority')
            ->get();

        return response()->json([
            'success' => true,
            'subtasks' => $subtasks
        ]);
    }

    /** CREATE SUBTASK */
    public function store(Request $request, $projectId, $kanban)
    {
        $userId = Auth::user()->id;

        // RBAC: cek akses membuat timeline
        if (!$this->rbacService->userHasKeyAccess($userId, 'timeline.create')) {
            return $this->denyAccess($request);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
        ]);

        // Hitung durasi
        $duration = null;
        if ($request->date_start && $request->date_end) {
            $duration = Carbon::parse($request->date_start)
                ->diffInDays(Carbon::parse($request->date_end));
        }

        // PERBAIKAN: Pastikan date dalam format yang benar
        $dateStart = $request->date_start ? Carbon::parse($request->date_start)->format('Y-m-d') : null;
        $dateEnd = $request->date_end ? Carbon::parse($request->date_end)->format('Y-m-d') : null;

        $subtask = Subtask::create([
            'id' => Str::uuid(),
            'kanbanId' => $kanban,
            'title' => $request->title,
            'description' => $request->description,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'duration' => $duration,
            'priority' => $request->priority,
            'status' => 'todo',
        ]);

        // Return JSON response for AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'subtask' => $subtask,
                'message' => 'Subtask berhasil ditambahkan.'
            ]);
        }

        return redirect()->back()->with('success', 'Subtask berhasil ditambahkan.');
    }

    /** UPDATE SUBTASK */
    public function update(Request $request, $project, $kanban, $subtask)
    {
        $userId = Auth::user()->id;

        // RBAC: cek akses membuat timeline
        if (!$this->rbacService->userHasKeyAccess($userId, 'timeline.create')) {
            return $this->denyAccess($request);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'status' => 'required|in:todo,inprogress,finished',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
        ]);

        $model = Subtask::where('kanbanId', $kanban)
            ->where('id', $subtask)
            ->firstOrFail();

        // Hitung durasi
        $duration = null;
        if ($request->date_start && $request->date_end) {
            $duration = Carbon::parse($request->date_start)
                ->diffInDays(Carbon::parse($request->date_end));
        }

        // PERBAIKAN: Pastikan date dalam format yang benar
        $dateStart = $request->date_start ? Carbon::parse($request->date_start)->format('Y-m-d') : null;
        $dateEnd = $request->date_end ? Carbon::parse($request->date_end)->format('Y-m-d') : null;

        $model->update([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => $request->status,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'duration' => $duration,
        ]);

        // Return JSON response for AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'subtask' => $model,
                'message' => 'Subtask berhasil diperbarui.'
            ]);
        }

        return redirect()->back()->with('success', 'Subtask berhasil diperbarui.');
    }

    /** DELETE SUBTASK */
    public function delete($project, $kanban, $subtask, $request)
    {
        $userId = Auth::user()->id;

        // RBAC: cek akses membuat timeline
        if (!$this->rbacService->userHasKeyAccess($userId, 'timeline.create')) {
            return $this->denyAccess($request);
        }

        $deleted = Subtask::where('kanbanId', $kanban)
            ->where('id', $subtask)
            ->delete();

        return response()->json([
            'success' => (bool)$deleted,
            'message' => $deleted ? 'Subtask berhasil dihapus.' : 'Subtask tidak ditemukan.'
        ]);
    }

    /** TOGGLE STATUS SUBTASK */
    public function toggleStatus(Request $request, $project, $kanban, $subtask)
    {
        $userId = Auth::user()->id;

        // RBAC: cek akses membuat timeline
        if (!$this->rbacService->userHasKeyAccess($userId, 'timeline.create')) {
            return $this->denyAccess($request);
        }
        $model = Subtask::where('kanbanId', $kanban)
            ->where('id', $subtask)
            ->firstOrFail();

        $model->update([
            'status' => $request->status ?? 'todo'
        ]);

        return response()->json([
            'success' => true,
            'subtask' => $model,
            'message' => 'Status berhasil diperbarui.'
        ]);
    }
}
