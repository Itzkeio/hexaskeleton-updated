<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Kanban;
use App\Models\Projects;
use Carbon\Carbon;

class KanbanController extends Controller
{
    public function index($projectId)
    {
        // ambil project + semua relasi kanban langsung
        $project = Projects::with('kanban')->findOrFail($projectId);

        return view('kanban.index', compact('project'));
    }

    public function store(Request $request, $projectId)
    {
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
            'description' => $request->description,
            'priority'   => $request->priority,
            'status'     => 'todo',
        ]);

        return redirect()->back()->with('success', 'Task berhasil ditambahkan.');
    }

    public function update(Request $request, $projectId, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
        ]);

        $task = Kanban::where('projectId', $projectId)
            ->where('id', $id)
            ->first();

        if (!$task) {
            return redirect()->back()->with('error', 'Task tidak ditemukan.');
        }

        // Hitung duration otomatis
        $duration = null;

        if ($request->date_start && $request->date_end) {
            $duration = Carbon::parse($request->date_start)
                ->diffInDays(Carbon::parse($request->date_end));
        }

        $updated = $task->update([
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority,
            'date_start'  => $request->date_start,
            'date_end'    => $request->date_end,
            'duration'    => $duration,
        ]);

        if ($updated) {
            return redirect()->back()->with('success', 'Task berhasil diperbarui.');
        } else {
            return redirect()->back()->with('error', 'Gagal memperbarui task.');
        }
    }

    public function updateStatus(Request $request, $projectId)
    {
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
        $deleted = Kanban::where('id', $id)
            ->where('projectId', $projectId)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', 'Task berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Task tidak ditemukan.');
        }
    }
}
