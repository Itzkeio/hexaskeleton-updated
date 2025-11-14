<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LogService;
use App\Models\Role;
use App\Models\RoleRbac;
use App\Services\RbacService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    protected $logService;
    protected $rbacService;

    public function __construct(RbacService $rbacService, LogService $logService)
    {
        $this->rbacService = $rbacService;
        $this->logService = $logService;
    }

    public function index()
    {
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.role');
        if (!$hasAccess) {
            return view('access-denied');
        }

        return view('masterdata.role.index');
    }

    public function datatable(Request $request)
    {
        $userId = Auth::id();
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.role');

        if (!$hasAccess) {
            // Return empty DataTables response
            return DataTables::of(collect([]))->make(true);
        }

        if ($request->ajax()) {
            $data = Role::query()->orderBy('name', 'ASC');

            // Global search (case-insensitive using LOWER)
            if ($request->has('search') && $request->search['value'] != '') {
                $search = strtolower($request->search['value']);

                $data->where(function ($query) use ($search) {
                    $query->whereRaw('LOWER("compName") LIKE ?', ["%{$search}%"])
                        ->orwhereRaw('LOWER("name") LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER("description") LIKE ?', ["%{$search}%"]);
                });
            }
        }

        return DataTables::of($data)
            ->addColumn('action', function ($role) {
                $viewRoute = route('role.show', ['id' => $role->id]);
                $editRoute = route('role.edit', ['id' => $role->id]);
                $deleteId = $role->id;

                $buttons = '<div class="btn-group dropend d-flex align-middle">
                <button type="button" class="btn btn-link dropdown-toggle p-0 text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="fa fa-bars"></span>
                </button>
                <ul class="dropdown-menu">';

                // Replace hardcoded URLs with named route links
                $buttons .= '<li><a class="dropdown-item py-1 act-menu" href="' . $viewRoute . '"><span class="fa fa-eye"></span> &nbsp; View</a></li>';
                $buttons .= '<li><a class="dropdown-item py-1 act-menu" href="' . $editRoute . '"><span class="fa fa-edit"></span> &nbsp; Edit</a></li>';
                $buttons .= '<li><a class="dropdown-item py-1 act-menu delete-btn" href="javascript:;" data-id="' . $deleteId . '"><span class="fa fa-trash"></span> &nbsp; Delete</a></li>';

                $buttons .= '</ul></div>';

                return $buttons;
            })
            ->editColumn('description', function ($role) {
                return $role->description ?: '-';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'create.role');
        if (!$hasAccess) {
            return view('access-denied');
        }

        return view('masterdata.role.create');
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'create.role');

        if (!$hasAccess) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'compCode'    => 'required|string',
            'compName'    => 'required|string',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'menuId'      => 'nullable|array',
        ]);

        try {
            // Simulasikan Auth::user() seperti FindFirstValue
            $user = Auth::user();
            $compCode = $user->compCode ?? null;
            $compName = $user->compName ?? null;
            $upn      = $user->userPrincipalName ?? null;

            // Uncomment if you implement RBAC permission check
            // if (!auth()->user()->hasAccess('role.create')) {
            //     return response()->json(['status' => 403, 'message' => "You don't have access."], 403);
            // }

            // Gunakan transaksi database
            DB::beginTransaction();

            $role = Role::create([
                'id'          => Str::uuid(),
                'compCode'    => $validated['compCode'],
                'compName'    => $validated['compName'],
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'createdAt'   => now(),
                'updatedAt'   => now(),
            ]);

            // Simpan RBAC list
            $rbacList = collect($validated['menuId'])->map(function ($key) use ($role) {
                return [
                    'roleId'  => $role->id,
                    'keyname' => $key,
                ];
            })->toArray();

            RoleRbac::insert($rbacList);

            // Tambah log
            $this->logService->addLog($upn, $compCode, $compName, 'Role', "Create new role: {$role->name}");

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Save role success.',
                'data'    => $role
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Store Role Error: " . $e->getMessage());

            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.role');
        if (!$hasAccess) {
            return view('access-denied');
        }

        $role = Role::findOrFail($id);
        return view('masterdata.role.view', compact('role'));
    }

    public function edit($id)
    {
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'edit.role');
        if (!$hasAccess) {
            return view('access-denied');
        }

        $role = Role::findOrFail($id);
        return view('masterdata.role.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'edit.role');

        if (!$hasAccess) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'compCode'   => 'required|string',
            'compName'   => 'required|string',
            'name'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'menuId'     => 'nullable|array',
            'menuId.*'   => 'string'
        ]);

        DB::beginTransaction();

        try {
            $role = Role::findOrFail($id);

            $role->compCode    = $validated['compCode'];
            $role->compName    = $validated['compName'];
            $role->name        = $validated['name'];
            $role->description = $validated['description'] ?? null;
            $role->save();

            // Sync RBAC permissions (assuming RoleRbac pivot table)
            // Delete old permissions
            RoleRbac::where('roleId', $role->id)->delete();

            // Insert new permissions
            $rbacList = collect($validated['menuId'])->map(function ($key) use ($role) {
                return [
                    'roleId'  => $role->id,
                    'keyname' => $key,
                ];
            })->toArray();

            RoleRbac::insert($rbacList);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Role updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Update Role Error: " . $e->getMessage());

            return response()->json([
                'status'  => 500,
                'message' => 'Failed to update role.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $userId = Auth::id();
            $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'delete.role');

            if (!$hasAccess) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $role = Role::findOrFail($id);
            $role->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to delete role ID {$id}: " . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to delete role'
            ], 500);
        }
    }
}
