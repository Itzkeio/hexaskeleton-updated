<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Projects;

class CalendarController extends Controller
{
    public function calendarPage()  {
        $users = User::all();
        return view('calendar.index', compact('users'));
    }

    public function getProjectByPic($picId){
        $projects = Projects::where('picId', $picId)->get();

        $events = $projects->map(function($project){
            return[
                'id'=>$project->id,
                'title'=>$project->name,
                'start'=>$project->start_date,
                'end'=>$project->end_date,
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

        // Version display
        'version'     => optional($p->version)->version ?? '-',

        // Timeline progress
        'progress'    => $p->getOverallProgress(),

        'dampak'      => $p->dampak,

        // 🔥 hanya tampilkan status terakhir (status aktual)
        'status'      => $currentStatus,
    ]);
}


}
