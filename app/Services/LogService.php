<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogService
{
    /**
     * Add a log entry to the database.
     *
     * @param string $userPrincipalName
     * @param string $compCode
     * @param string $compName
     * @param string $activity
     * @param string $description
     * @return void
     */
    public function addLog(
        string $userPrincipalName,
        string $compCode,
        string $compName,
        string $activity,
        string $description
    ): void {
        Log::create([
            'username'    => $userPrincipalName,
            'compCode'    => $compCode,
            'compName'    => $compName,
            'activity'    => $activity,
            'description' => $description,
            'createdAt'   => now(), // If you're using custom timestamp column
        ]);
    }

    /**
     * Shortcut to log with current authenticated user.
     */
    public function logAuthUser(string $activity, string $description): void
    {
        $user = Auth::user();

        if ($user) {
            $this->addLog(
                $user->userPrincipalName ?? '',
                $user->compCode ?? '',
                $user->compName ?? '',
                $activity,
                $description
            );
        }
    }
}
