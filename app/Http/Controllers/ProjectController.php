<?php

namespace App\Http\Controllers;

use App\Models\Groups;
use App\Models\Projects;
use App\Models\Versions;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\RbacService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{

     protected $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    public function index(Request $request)
{
    // Cek akses RBAC
    $userId = Auth::user()->id;
    $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.projects');

    if (!$hasAccess) {
        return view('access-denied');
    }

    // Ambil data projects
    $projects = Projects::with(['version', 'timeline', 'versions'])
        ->orderBy('id', 'asc')
        ->get();

    $selectedProject = $projects->first();

    return view('project-mgt.projects', compact('projects', 'selectedProject'));
}

    public function search(Request $request)
    {
        $query = Projects::with(['version', 'timeline', 'versions']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $projects = $query->orderBy('id', 'asc')->get();

        if ($request->ajax()) {
            return view('project-mgt.partials.project-list', compact('projects'))->render();
        }

        $selectedProject = $projects->first();
        return view('project-mgt.projects', compact('projects', 'selectedProject'));
    }

    public function create()
    {
        $users = User::all();
        return view('project-mgt.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'createdAt' => 'required|date',
            'finishedAt' => 'nullable|date|after_or_equal:createdAt',
            'dampak' => 'nullable|string',
            'version' => 'required|string|max:50',
            'picType' => 'required|in:individual,group',
            'picUser' => 'nullable|exists:users,id',
            'groupName' => 'nullable|string|max:255',
            'groupMembers' => 'nullable|array',
            'groupMembers.*' => 'exists:users,id',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'nullable|in:0,1',
        ]);

        DB::beginTransaction();

        try {
            if ($validated['picType'] === 'individual') {
                if (!$validated['picUser']) {
                    return back()->withErrors(['picUser' => 'Pilih user PIC untuk tipe individual.'])->withInput();
                }
                $picId = $validated['picUser'];
            } else {
                if (!$validated['groupName']) {
                    return back()->withErrors(['groupName' => 'Masukkan nama group.'])->withInput();
                }
                $group = Groups::create(['name' => $validated['groupName']]);

                if (!empty($validated['groupMembers'])) {
                    foreach ($validated['groupMembers'] as $userId) {
                        DB::table('group_members')->insert([
                            'group_id' => $group->id,
                            'user_id' => $userId,
                        ]);
                    }
                }

                $picId = $group->id;
            }

            $iconName = null;
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
                $iconName = basename($path);
            }

            $project = Projects::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'picId' => $picId,
                'picType' => $validated['picType'],
                'icon' => $iconName,
                'dampak' => $validated['dampak'],
                'createdAt' => $validated['createdAt'],
                'finishedAt' => $validated['finishedAt'] ?? null,
            ]);

            $version = Versions::create([
                'projectId' => $project->id,
                'version' => $validated['version'],
                'description' => $validated['description'],
                'status' => isset($validated['status']) && $validated['status'] == 1,
                // ✅ Hanya set releasedAt jika column ada di database
                // 'releasedAt' => now()
            ]);

            $project->update(['versionId' => $version->id]);

            DB::commit();

            return redirect()
                ->route('projects.index')
                ->with('success', 'Project berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $project = Projects::with(['version', 'versions'])->findOrFail($id);
        $users = User::all();

        $groupMembers = [];
        if ($project->picType === 'group') {
            $groupMembers = DB::table('group_members')
                ->where('group_id', $project->picId)
                ->pluck('user_id')
                ->toArray();

            $group = Groups::find($project->picId);
            $project->groupName = $group ? $group->name : '';
        }

        return view('project-mgt.edit', compact('project', 'users', 'groupMembers'));
    }

    public function update(Request $request, $id)
    {
        $project = Projects::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'createdAt' => 'required|date',
            'finishedAt' => 'nullable|date|after_or_equal:createdAt',
            'dampak' => 'nullable|string',
            'version' => 'required|string|max:50',
            'picType' => 'required|in:individual,group',
            'picUser' => 'nullable|exists:users,id',
            'groupName' => 'nullable|string|max:255',
            'groupMembers' => 'nullable|array',
            'groupMembers.*' => 'exists:users,id',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:0,1',
            'remove_icon' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $oldPicType = $project->picType;
            $oldPicId = $project->picId;

            if ($validated['picType'] === 'individual') {
                if (!$validated['picUser']) {
                    return back()->withErrors(['picUser' => 'Pilih user PIC untuk tipe individual.'])->withInput();
                }
                $picId = $validated['picUser'];

                if ($oldPicType === 'group') {
                    $this->deleteGroup($oldPicId);
                }
            } else {
                if (!$validated['groupName']) {
                    return back()->withErrors(['groupName' => 'Masukkan nama group.'])->withInput();
                }

                if ($oldPicType === 'group') {
                    $group = Groups::find($oldPicId);
                    if ($group) {
                        $group->update(['name' => $validated['groupName']]);
                        DB::table('group_members')->where('group_id', $group->id)->delete();
                    } else {
                        $group = Groups::create(['name' => $validated['groupName']]);
                    }
                } else {
                    $group = Groups::create(['name' => $validated['groupName']]);
                }

                if (!empty($validated['groupMembers'])) {
                    foreach ($validated['groupMembers'] as $userId) {
                        DB::table('group_members')->insert([
                            'group_id' => $group->id,
                            'user_id' => $userId,
                        ]);
                    }
                }

                $picId = $group->id;
            }

            $iconName = $project->icon;

            if ($request->has('remove_icon') && $request->remove_icon) {
                if ($project->icon) {
                    Storage::delete('public/icons/' . $project->icon);
                    $iconName = null;
                }
            }

            if ($request->hasFile('icon')) {
                if ($project->icon) {
                    Storage::delete('public/icons/' . $project->icon);
                }
                $path = $request->file('icon')->store('public/icons');
                $iconName = basename($path);
            }

            $project->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'picId' => $picId,
                'picType' => $validated['picType'],
                'icon' => $iconName,
                'dampak' => $validated['dampak'],
                'createdAt' => $validated['createdAt'],
                'finishedAt' => $validated['finishedAt'] ?? null,
            ]);

            if ($project->version) {
                $project->version->update([
                    'version' => $validated['version'],
                    'description' => $validated['description'],
                    'status' => $validated['status'] == '1' || $validated['status'] == 1, // Boolean conversion
                ]);
            } else {
                $version = Versions::create([
                    'projectId' => $project->id,
                    'version' => $validated['version'],
                    'description' => $validated['description'],
                    'status' => $validated['status'] == '1' || $validated['status'] == 1,
                ]);
                $project->update(['versionId' => $version->id]);
            }

            DB::commit();

            return redirect()
                ->route('projects.index')
                ->with('success', 'Project berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // ✨ NEW: Method untuk menambah version baru
    public function addVersion(Request $request, $id)
    {
        $project = Projects::with('versions')->findOrFail($id);

        $validated = $request->validate([
            'version' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        DB::beginTransaction();

        try {
            // Jika version baru di-set active, nonaktifkan semua version lain
            if ($validated['status'] == 1) {
                Versions::where('projectId', $project->id)
                    ->update(['status' => false]);
            }

            // Buat version baru
            $newVersion = Versions::create([
                'projectId' => $project->id,
                'version' => $validated['version'],
                'description' => $validated['description'],
                'status' => (bool) $validated['status'],
                // ✅ Hanya set releasedAt jika column ada di database
                // 'releasedAt' => now()
            ]);

            // Update versionId di project jika version baru active
            if ($validated['status'] == 1) {
                $project->update(['versionId' => $newVersion->id]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Version baru berhasil ditambahkan!'
                ]);
            }

            return redirect()
                ->route('projects.index')
                ->with('success', 'Version baru berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // ✨ NEW: Method untuk edit version
    public function editVersion(Request $request, $projectId, $versionId)
    {
        $project = Projects::findOrFail($projectId);
        $version = Versions::where('projectId', $projectId)
            ->where('id', $versionId)
            ->firstOrFail();

        $validated = $request->validate([
            'version' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        DB::beginTransaction();

        try {
            // Jika version di-set active, nonaktifkan semua version lain
            if ($validated['status'] == 1 && !$version->status) {
                Versions::where('projectId', $projectId)
                    ->where('id', '!=', $versionId)
                    ->update(['status' => false]);
            }

            // Update version
            $version->update([
                'version' => $validated['version'],
                'description' => $validated['description'],
                'status' => (bool) $validated['status'],
            ]);

            // Update versionId di project jika version ini active
            if ($validated['status'] == 1) {
                $project->update(['versionId' => $version->id]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Version berhasil diupdate!'
                ]);
            }

            return redirect()
                ->route('projects.index')
                ->with('success', 'Version berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // ✨ NEW: Method untuk delete version
    public function deleteVersion(Request $request, $projectId, $versionId)
    {
        $project = Projects::findOrFail($projectId);
        $version = Versions::where('projectId', $projectId)
            ->where('id', $versionId)
            ->firstOrFail();

        // Cek apakah ini version terakhir
        $versionCount = Versions::where('projectId', $projectId)->count();
        if ($versionCount <= 1) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa menghapus version terakhir!'
                ], 400);
            }
            return back()->withErrors(['error' => 'Tidak bisa menghapus version terakhir!']);
        }

        DB::beginTransaction();

        try {
            $wasActive = $version->status;

            // Hapus version
            $version->delete();

            // Jika yang dihapus adalah active version, set version terbaru sebagai active
            if ($wasActive) {
                $latestVersion = Versions::where('projectId', $projectId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($latestVersion) {
                    $latestVersion->update(['status' => true]);
                    $project->update(['versionId' => $latestVersion->id]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Version berhasil dihapus!'
                ]);
            }

            return redirect()
                ->route('projects.index')
                ->with('success', 'Version berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ✨ NEW: Method untuk set version sebagai active
    public function setActiveVersion(Request $request, $projectId, $versionId)
    {
        $project = Projects::findOrFail($projectId);
        $version = Versions::where('projectId', $projectId)
            ->where('id', $versionId)
            ->firstOrFail();

        DB::beginTransaction();

        try {
            // Nonaktifkan semua version lain
            Versions::where('projectId', $projectId)
                ->update(['status' => false]);

            // Aktifkan version yang dipilih
            $version->update(['status' => true]);

            // Update versionId di project
            $project->update(['versionId' => $version->id]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Version berhasil diaktifkan!'
                ]);
            }

            return redirect()
                ->route('projects.index')
                ->with('success', 'Version berhasil diaktifkan!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $project = Projects::findOrFail($id);

        DB::beginTransaction();

        try {
            if ($project->icon) {
                Storage::delete('public/icons/' . $project->icon);
            }

            if ($project->picType === 'group') {
                $this->deleteGroup($project->picId);
            }

           DB::table('timeline')->where('projectId', $project->id)->delete();
            // Hapus semua versions
            Versions::where('projectId', $project->id)->delete();

            $project->delete();

            DB::commit();

            return redirect()
                ->route('projects.index')
                ->with('success', 'Project berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('projects.index')
                ->withErrors(['error' => 'Gagal menghapus project: ' . $e->getMessage()]);
        }
    }

    private function deleteGroup($groupId)
    {
        DB::table('group_members')->where('group_id', $groupId)->delete();
        Groups::where('id', $groupId)->delete();
    }
}
