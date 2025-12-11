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

    public function getProjectByPic($picId)
{
    // Ambil semua project di mana user adalah PIC individual
    $individual = Projects::where('picType', 'individual')
        ->where('picId', $picId);

    // Ambil semua project di mana user menjadi anggota group PIC
    $group = Projects::where('picType', 'group')
        ->whereIn('id', function($q) use ($picId) {
            $q->select('projectId')
              ->from('groups')
              ->whereIn('id', function($q2) use ($picId) {
                  $q2->select('group_id')
                     ->from('group_members')
                     ->where('user_id', $picId);
              });
        });

    // Gabungkan
    $projects = $individual->union($group)->get();


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
