<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Services\LogService;
use App\Services\RbacService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
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
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.employee');
        if (!$hasAccess) {
            return view('access-denied');
        }

        return view('masterdata.employee.index');
    }

    public function datatable()
    {
        $userId = Auth::id();
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.employee');

        if (!$hasAccess) {
            // Return empty DataTables response
            return DataTables::of(collect([]))->make(true);
        }

        $data = User::with('roles');

        return DataTables::of($data)
            ->addColumn('action', function ($emp) {
                $viewRoute = route('employee.show', ['id' => $emp->id]);
                $editRoute = route('employee.edit', ['id' => $emp->id]);

                $buttons = '<div class="btn-group dropend d-flex align-middle">
                <button type="button" class="btn btn-link dropdown-toggle p-0 text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="fa fa-bars"></span>
                </button>
                <ul class="dropdown-menu">';

                // Replace hardcoded URLs with named route links
                $buttons .= '<li><a class="dropdown-item py-1 act-menu" href="' . $viewRoute . '"><span class="fa fa-eye"></span> &nbsp; View</a></li>';
                $buttons .= '<li><a class="dropdown-item py-1 act-menu" href="' . $editRoute . '"><span class="fa fa-edit"></span> &nbsp; Assign Role</a></li>';

                $buttons .= '</ul></div>';

                return $buttons;
            })
            ->addColumn('roleName', function ($emp) {
                return $emp->roles->pluck('name')->implode(', ') ?: '-';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.employee');
        if (!$hasAccess) {
            return view('access-denied');
        }

        $employee = User::with('roles')->findOrFail($id);
        return view('masterdata.employee.view', compact('employee'));
    }

    public function edit($id)
    {
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'edit.employee');
        if (!$hasAccess) {
            return view('access-denied');
        }

        $user = User::with('roles')->findOrFail($id);
        $availableRoles = Role::all();
        $selectedRoleIds = $user->roles->pluck('id')->toArray();

        return view('masterdata.employee.edit', compact('user', 'availableRoles', 'selectedRoleIds'));
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'edit.employee');

        if (!$hasAccess) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'selectedRoleIds' => 'required|array',
            'selectedRoleIds.*' => 'uuid|exists:roles,id',
        ]);

        DB::beginTransaction();

        try {
            // Optional: check user exists
            $user = User::findOrFail($id);

            // Delete old roles (custom pivot table)
            DB::table('userRoles')->where('userId', $user->id)->delete();

            // Insert new role mappings
            $now = now();
            $roles = collect($validated['selectedRoleIds'])->map(function ($roleId) use ($user, $now) {
                return [
                    'userId'    => $user->id,
                    'roleId'    => $roleId,
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ];
            })->toArray();

            DB::table('userRoles')->insert($roles);

            // Tambah log
            $user = Auth::user();
            $compCode = $user->compCode ?? null;
            $compName = $user->compName ?? null;
            $upn      = $user->userPrincipalName ?? null;
            $this->logService->addLog($upn, $compCode, $compName, 'Employee', "Update employee data: {$user->name}");

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Roles successfully assigned.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Failed to assign roles: ' . $e->getMessage(),
            ], 500);
        }
    }
}
