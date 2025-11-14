<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function index()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard'); // redirect if logged in
        }

        return view('auth.index');
    }

    public function doLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $apiUrl = config('services.sso.url') . '/api/v1/GlobalLogin/Login';
        $appId = config('services.sso.app_id');
        $appKey = config('services.sso.app_key');

        $payload = [
            'username'   => $request->email,
            'password'   => $request->password,
            'getProfile' => true,
        ];

        try {
            DB::beginTransaction();

            $response = Http::withHeaders([
                'app_id'  => $appId,
                'app_key' => $appKey,
            ])->post($apiUrl, $payload);

            if ($response->failed()) {
                $this->logService->addLog($request->email, '', '', 'Login', 'Wrong username or password');
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $responseData = $response->json();
            $accessToken = $responseData['accessToken'] ?? null;

            if (!$accessToken) {
                return response()->json(['error' => 'Token missing'], 400);
            }

            // Decode JWT payload
            $tokenParts = explode('.', $accessToken);
            if (count($tokenParts) !== 3) {
                return response()->json(['error' => 'Invalid token format'], 400);
            }

            $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);

            if (!$payload) {
                return response()->json(['error' => 'Invalid token payload'], 400);
            }

            // Map user data from payload
            $userData = [
                'compCode'           => $payload['CompCode'] ?? '',
                'compName'           => $payload['CompName'] ?? '',
                'divCode'            => $payload['DivCode'] ?? '',
                'divName'            => $payload['DivName'] ?? '',
                'userPrincipalName'  => $payload['UserPrincipalName'] ?? '',
                'email'              => $payload['Email'] ?? '',
                'nik'                => $payload['NIK'] ?? '',
                'name'               => $payload['Name'] ?? '',
                'empTypeGroup'       => $payload['EmpTypeGroup'] ?? '',
                'jobLvlName'         => $payload['JobLvlName'] ?? '',
                'jobTtlName'         => $payload['JobTtlName'] ?? '',
                'deptName'           => $payload['DeptName'] ?? '',
            ];


            // Save or update user
            $user = User::updateOrCreate(
                ['userPrincipalName' => $userData['userPrincipalName']],
                $userData
            );

            // Reload to ensure it was saved and has ID
            $user = User::where('userPrincipalName', $userData['userPrincipalName'])->first();

            if (!$user || !$user->id) {
                DB::rollBack();
                Log::error('User creation failed', ['userData' => $userData]);
                return response()->json(['error' => 'User creation failed'], 500);
            }

            // Commit before login to ensure user is persisted
            DB::commit();

            // Log in the user
            Auth::login($user, false); // 'true' for remember

            // Log the login
            $this->logService->addLog(
                $user->userPrincipalName,
                $user->compCode,
                $user->compName,
                'Login',
                'User logged in'
            );

            return response()->json([
                'message'     => 'Login successful.',
                'redirectUrl' => route('dashboard'), // or any default
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        // Get user details before logging out
        $user = Auth::user();

        if ($user) {
            $email = $user->userPrincipalName ?? $user->email ?? 'unknown';
            $compCode = $user->compCode ?? '';
            $compName = $user->compName ?? '';

            // Log the logout action
            $this->logService->addLog($email, $compCode, $compName, 'Logout', 'User logged out');
        }

        // Logout the user
        Auth::logout();

        // Invalidate session
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login'); // adjust route name as needed
    }
}
