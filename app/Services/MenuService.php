<?php
// app/Services/MenuService.php

namespace App\Services;

use App\Models\RoleRbac;

class MenuService
{
    public function getMenuTree($parentId)
    {
        return $this->getNodes($parentId, null);
    }

    public function getMenuTreeWithRbac($parentId, $roleId)
    {
        $assignedKeys = RoleRbac::where('roleId', $roleId)->pluck('keyname')->toArray();
        return $this->getNodes($parentId, $assignedKeys);
    }

    private function getNodes($parentId, $assignedKeys = null)
    {
        $data = [
            '#' => [
                $this->rbacNode('1', '#', 'Home', null, true, $assignedKeys),
                $this->rbacNode('2', '#', 'Master Data', null, true, $assignedKeys),
                $this->rbacNode('3', '#', 'Log', null, true, $assignedKeys),
            ],
            '1' => [
                $this->rbacNode('1-1', '1', 'Dashboard', 'view.dashboard', false, $assignedKeys),
            ],
            '2' => [
                $this->rbacNode('2-1', '2', 'Role', null, true, $assignedKeys),
                $this->rbacNode('2-2', '2', 'Employee', null, true, $assignedKeys),
            ],
            '2-1' => [
                $this->rbacNode('2-1-1', '2-1', 'View Role', 'view.role', false, $assignedKeys),
                $this->rbacNode('2-1-2', '2-1', 'Create Role', 'create.role', false, $assignedKeys),
                $this->rbacNode('2-1-3', '2-1', 'Edit Role', 'edit.role', false, $assignedKeys),
                $this->rbacNode('2-1-4', '2-1', 'Delete Role', 'delete.role', false, $assignedKeys),
            ],
            '2-2' => [
                $this->rbacNode('2-2-1', '2-2', 'View Employee', 'view.employee', false, $assignedKeys),
                $this->rbacNode('2-2-2', '2-2', 'Edit Employee', 'edit.employee', false, $assignedKeys),
            ],
            '3' => [
                $this->rbacNode('3-1', '3', 'Audit Trail', 'view.audittrail', false, $assignedKeys),
            ],
        ];

        return $data[$parentId] ?? [];
    }

    private function rbacNode($id, $parent, $text, $key = null, $hasChildren = false, $assignedKeys = null)
    {
        $state = [
            'opened' => false,
            'selected' => false,
        ];

        if (!empty($key) && $assignedKeys && in_array($key, $assignedKeys)) {
            $state['selected'] = true;
        }

        if ($hasChildren || $state['selected']) {
            $state['opened'] = true;
        }

        return [
            'id' => $id,
            'parent' => $parent,
            'text' => $text,
            'key' => $key,
            'children' => $hasChildren,
            'state' => $state,
        ];
    }
}
