<?php

namespace App\Http\Controllers;

use App\Services\RbacService;
use App\Models\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\KanbanStatus;
use Illuminate\Support\Facades\Auth;

class KanbanStatusController extends Controller
{
     protected $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }
    /**
     * Halaman manage status
     */
    public function index(Projects $project)
    {
         // Cek akses RBAC
        // Cek akses RBAC
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.kanbanStatus');

        if (!$hasAccess) {
            return view('access-denied');
        }

        return view('project-mgt.kanban.status.index', [
            'project'   => $project,
            'statuses'  => $project->statuses()
                                   ->withTrashed()
                                   ->orderBy('order')
                                   ->get(),
        ]);
    }

    /**
     * Tambah status baru
     */
    public function store(Request $req, Projects $project)
    {
        $req->validate([
            'label' => 'required|string|max:50',
        ]);

        $key = Str::slug($req->label);

        // slug unik per proyek
        if ($project->statuses()->withTrashed()->where('key', $key)->exists()) {
            return back()->with('error', 'Status dengan nama tersebut sudah ada.');
        }

        KanbanStatus::create([
            'projectId'     => $project->id,
            'key'           => $key,
            'label'         => $req->label,
            'color_bg'      => $req->color_bg ?? '#e9ecef',
            'color_border'  => $req->color_border ?? '#bfbfbf',
            'order'         => $project->statuses()->count(),
        ]);

        KanbanStatus::createLog([
            'projectId'     => $project->id,
            'key'           => $key,
            'label'         => $req->label,
            'color_bg'      => $req->color_bg ?? '#e9ecef',
            'color_border'  => $req->color_border ?? '#bfbfbf',
            'order'         => $project->statuses()->count(),
        ]);

        return back()->with('success', 'Status berhasil dibuat!');
    }

    /**
     * Update status
     */
    public function update(Request $req, Projects $project, KanbanStatus $status)
    {
        if ($status->projectId != $project->id) {
            abort(403);
        }

        $req->validate([
            'label' => 'required|string|max:50',
        ]);

        $status->update([
            'label'        => $req->label,
            'color_bg'     => $req->color_bg,
            'color_border' => $req->color_border,
        ]);

        return back()->with('success', 'Status berhasil diperbarui!');
    }

    /**
     * Soft delete status
     */
    public function destroy(Projects $project, KanbanStatus $status)
    {
        if ($status->projectId !== $project->id) {
            abort(403);
        }

        // Tidak boleh hapus status yang dipakai task
        if ($status->kanbans()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus status yang sedang digunakan task.');
        }

        $status->delete();

        return back()->with('success', 'Status berhasil dihapus!');
    }

    /**
     * Restore status yang di-softdelete
     */
    public function restore(Projects $project, $id)
    {
        $status = KanbanStatus::onlyTrashed()
            ->where('projectId', $project->id)
            ->where('id', $id)
            ->firstOrFail();

        $status->restore();

        return back()->with('success', 'Status berhasil direstore!');
    }

    /**
     * Update urutan status
     */
    public function updateOrder(Request $req, Projects $project)
    {
        foreach ($req->order as $index => $id) {
            KanbanStatus::where('id', $id)
                ->where('projectId', $project->id)
                ->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
