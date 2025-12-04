<?php

namespace App\Http\Controllers;

use App\Models\KanbanLog;

use App\Models\Projects;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KanbanLogController extends Controller
{
    public function index($projectId){
        $project = Projects::findOrFail($projectId);
        return view('project-mgt.kanban.kanban-logs', compact('project'));
    }

    public function datatable(Request $request, $projectId)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $logs = KanbanLog::where('projectId', $projectId)
            ->with('user')
            ->orderBy('created_at', 'DESC');

        return DataTables::of($logs)
            ->addIndexColumn()

            ->addColumn('username', function ($row) {
                return $row->user->name ?? '-';
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at
                    ? $row->created_at->format('Y-m-d H:i:s')
                    : '-';
            })

            ->make(true);
    }
}
