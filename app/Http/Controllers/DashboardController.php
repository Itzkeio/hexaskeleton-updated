<?php

namespace App\Http\Controllers;

use App\Services\RbacService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    public function index()
    {
        $userId = Auth::user()->id;
        $hasAccess = $this->rbacService->userHasKeyAccess($userId, 'view.dashboard');
        if (!$hasAccess) {
            return view('access-denied');
        }

        return view('dashboard');
    }
}
