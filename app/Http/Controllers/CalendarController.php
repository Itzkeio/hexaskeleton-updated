<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Projects;
use App\Models\Groups; // tambahkan ini

class CalendarController extends Controller
{
    public function calendarPage()
    {
        $users = User::all();
        $groupIds = Projects::where('picType', 'group')
            ->whereNotNull('picId')
            ->distinct()
            ->pluck('picId');

        $groups = Groups::whereIn(
    'id',
    Projects::where('picType','group')
        ->pluck('picId')
        ->unique()
)->orderBy('name')->get();
        // ambil semua group
        return view('calendar.index', compact('users', 'groups'));
    }

    // Method baru untuk ambil project by PIC (support individual & group)
    public function getProjectByPic(Request $request, $picId)
    {
        $picType = $request->query('type', 'individual'); // default individual

        if ($picType === 'group') {
            // Ambil HANYA project dengan PIC group
            $projects = Projects::where('picType', 'group')
                ->where('picId', $picId)
                ->get();
        } else {
            // Ambil HANYA project dengan PIC individual
            $projects = Projects::where('picType', 'individual')
                ->where('picId', $picId)
                ->get();
        }

        // Mapping ke event calendar
        $events = $projects->map(function ($project) {
            return [
                'id'    => $project->id,
                'title' => $project->name,
                'start' => $project->createdAt,
                'end'   => $project->finishedAt,
                'color' => '#3788d8',
            ];
        });

        return response()->json($events);
    }

    public function getProjectDetail($id)
    {
        $p = Projects::with(['version', 'actualTimelines', 'kanban'])
            ->findOrFail($id);

        // Status saat ini = status task terakhir
        $currentStatus = $p->kanban->last()?->status ?? '-';

        return response()->json([
            'id'          => $p->id,
            'name'        => $p->name,
            'description' => $p->description,
            'pic'         => $p->pic_name,
            'start'       => $p->createdAt,
            'end'         => $p->finishedAt,
            'version'     => optional($p->version)->version ?? '-',
            'progress'    => $p->getOverallProgress(),
            'dampak'      => $p->dampak,
            'status'      => $currentStatus,
        ]);
    }
}
