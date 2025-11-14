<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Show the application home page
     */
    public function index(): View
    {
        return view('home.index');
    }

    /**
     * Show the privacy page
     */
    public function privacy(): View
    {
        return view('home.privacy');
    }

    /**
     * Show error page with no caching
     */
    public function error(Request $request): View
    {
        return response()
            ->view('home.error', [
                'requestId' => $request->header('X-Request-ID') ?? Str::uuid()->toString()
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}