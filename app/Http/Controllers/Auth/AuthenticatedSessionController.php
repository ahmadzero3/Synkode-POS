<?php

namespace App\Http\Controllers\Auth;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Providers\RouteServiceProvider;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $redirectUrl = RouteServiceProvider::HOME;

        if ($user) {
            $isCashier = false;

            if (isset($user->role) && isset($user->role->name)) {
                $roleName = strtolower($user->role->name);
                if ($roleName === 'cashier') {
                    $isCashier = true;
                }
            }

            if (!$isCashier && property_exists($user, 'role_name')) {
                $roleName = strtolower($user->role_name ?? '');
                if ($roleName === 'cashier') {
                    $isCashier = true;
                }
            }

            if (!$isCashier && isset($user->role_id)) {
                if ((int)$user->role_id === 3) {
                    $isCashier = true;
                }
            }

            if ($isCashier) {
                $redirectUrl = '/pos';
            }
        }

        return response()->json([
            'status' => true,
            'message' => __('user.login_success'),
            'redirect_url' => $redirectUrl,
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
