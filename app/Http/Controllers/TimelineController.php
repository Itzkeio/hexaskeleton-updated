<?php

namespace App\Http\Controllers;

use App\Models\Timeline;
use App\Models\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\RbacService;

class TimelineController extends Controller
{
    protected $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }
   public function store(Request $request, $projectId)
    {
        $userId = Auth::user()->id;

        // RBAC: cek akses membuat timeline
        if (!$this->rbacService->userHasKeyAccess($userId, 'timeline.create')) {
            return $this->denyAccess($request);
        }

        $project = Projects::findOrFail($projectId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'progress' => 'required|integer|min:0|max:100',
        ]);

        try {
            $timeline = Timeline::create([
                'projectId' => $project->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'progress' => $validated['progress'],
                'type' => 'actual',
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Timeline berhasil ditambahkan!',
                    'data' => $timeline
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Timeline berhasil ditambahkan!');
        } catch (\Exception $e) {
            return $this->handleException($request, $e);
        }
    }

    /**
     * Update timeline
     */
    public function update(Request $request, $projectId, $timelineId)
    {
        $userId = Auth::user()->id;

        // RBAC: cek akses edit timeline
        if (!$this->rbacService->userHasKeyAccess($userId, 'timeline.update')) {
            return $this->denyAccess($request);
        }

        $project = Projects::findOrFail($projectId);
        $timeline = Timeline::where('projectId', $projectId)
            ->where('id', $timelineId)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'progress' => 'required|integer|min:0|max:100',
        ]);

        try {
            $timeline->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'progress' => $validated['progress'],
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Timeline berhasil diupdate!',
                    'data' => $timeline
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Timeline berhasil diupdate!');
        } catch (\Exception $e) {
            return $this->handleException($request, $e);
        }
    }

    /**
     * Delete timeline
     */
    public function destroy(Request $request, $projectId, $timelineId)
    {
        $userId = Auth::user()->id;

        // RBAC: cek akses delete timeline
        if (!$this->rbacService->userHasKeyAccess($userId, 'timeline.delete')) {
            return $this->denyAccess($request);
        }

        $project = Projects::findOrFail($projectId);
        $timeline = Timeline::where('projectId', $projectId)
            ->where('id', $timelineId)
            ->firstOrFail();

        try {
            $timeline->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Timeline berhasil dihapus!'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Timeline berhasil dihapus!');
        } catch (\Exception $e) {
            return $this->handleException($request, $e);
        }
    }

    /**
     * Get timeline data for Gantt Chart
     */
    public function getGanttData($projectId)
    {
        $userId = Auth::user()->id;

        // RBAC: cek akses melihat timeline gantt
        if (!$this->rbacService->userHasKeyAccess($userId, 'timeline.view')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $project = Projects::with('actualTimelines')->findOrFail($projectId);

        $ganttData = [];

        // Plan Awal
        if ($project->createdAt) {
            $ganttData[] = [
                'id' => 'plan-initial',
                'name' => 'Plan Awal: ' . $project->name,
                'start' => $project->createdAt,
                'end' => $project->finishedAt ?? now()->format('Y-m-d'),
                'progress' => 100,
                'type' => 'plan',
                'color' => '#e3f2fd'
            ];
        }

        // Actual Plans
        foreach ($project->actualTimelines as $timeline) {
            $ganttData[] = [
                'id' => 'timeline-' . $timeline->id,
                'name' => $timeline->title,
                'start' => $timeline->start_date?->format('Y-m-d') ?? '',
                'end' => $timeline->end_date?->format('Y-m-d') ?? '',
                'progress' => $timeline->progress,
                'type' => 'actual',
                'color' => $this->getProgressColor($timeline->progress),
                'description' => $timeline->description
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $ganttData,
            'project' => [
                'name' => $project->name,
                'overall_progress' => $project->getOverallProgress()
            ]
        ]);
    }

    /**
     * Helper: If no access → return appropriate response
     */
    private function denyAccess(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return view('access-denied');
    }

    /**
     * Helper: Handle exceptions for ajax/non-ajax
     */
    private function handleException(Request $request, \Exception $e)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

        return back()->withErrors(['error' => $e->getMessage()]);
    }

    private function getProgressColor($progress)
    {
        if ($progress >= 75) return '#4caf50';
        if ($progress >= 50) return '#ff9800';
        if ($progress >= 25) return '#ffc107';
        return '#f44336';
    }
}
