<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Log;
use App\Services\RbacService;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    // protected $rbacService;

    // public function __construct(RbacService $rbacService)
    // {
    //     $this->rbacService = $rbacService;
    // }

    public function index()
    {
        // $userId = Auth::user()->id;
        // $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.audittrail');
        // if (!$hasAccess) {
        //     return view('access-denied');
        // }

        return view('logs.index');
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = Log::query()->orderBy('createdAt', 'DESC');

            // Global search (case-insensitive using LOWER)
            if ($request->has('search') && $request->search['value'] != '') {
                $search = strtolower($request->search['value']);

                $data->where(function ($query) use ($search) {
                    $query->whereRaw('LOWER("compName") LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER("username") LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER("activity") LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER("description") LIKE ?', ["%{$search}%"]);
                });
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('createdAt', function ($row) {
                    return $row->createdAt
                        ? \Carbon\Carbon::parse($row->createdAt)->format('Y-m-d H:i:s')
                        : '-';
                })
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }
}
