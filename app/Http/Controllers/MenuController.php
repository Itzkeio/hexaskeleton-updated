<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MenuService;

class MenuController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function getMenuStructure(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $roleId = $request->input('roleId');

        if ($type === 'view') {
            $nodes = $this->menuService->getMenuTreeWithRbac($id, $roleId);
        } else {
            $nodes = $this->menuService->getMenuTree($id);
        }

        return response()->json($nodes);
    }
}
