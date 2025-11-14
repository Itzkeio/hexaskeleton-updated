<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RbacService
{
    public function userHasKeyAccess($userId, $keyname)
    {
        $user = User::with(['roles.rbac'])->find($userId);

        if (!$user) return false;

        foreach ($user->roles as $role) {
            foreach ($role->rbac as $rbac) {
                if ($rbac->keyname === $keyname) {
                    return true;
                }
            }
        }

        return false;
    }
}
