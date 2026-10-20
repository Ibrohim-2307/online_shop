<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the web login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request for web session.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $credentials['login'];

        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'login' => "Login yoki parol noto'g'ri.",
            ])->onlyInput('login');
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'login' => "Ushbu hisob faolsizlantirilgan. Administrator bilan bog'laning.",
            ])->onlyInput('login');
        }

        Auth::login($user);

        // Security requirement: regenerate session ID upon login to prevent session fixation
        $request->session()->regenerate();

        AuditService::log('web.login', $user);

        if ($user->hasRole(['admin', 'moderator'])) {
            return redirect()->intended('/admin');
        }

        if ($user->hasRole(['seller_owner', 'seller_manager', 'seller_operator'])) {
            return redirect()->intended('/seller');
        }

        return redirect()->intended('/profile');
    }

    /**
     * Destroy an authenticated web session.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user) {
            AuditService::log('web.logout', $user);
        }

        return redirect('/login');
    }
}
