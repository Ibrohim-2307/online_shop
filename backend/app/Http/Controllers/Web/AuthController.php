<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SellerMember;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

        $demoEmails = ['admin@market.uz', 'seller@market.uz', 'customer@market.uz', 'courier@market.uz'];
        $isDemoAccount = $user && in_array($user->email, $demoEmails, true);
        $allowDemoFailsafe = app()->environment('local', 'testing') || config('app.demo_mode');

        if ($user && !$user->is_active) {
            return back()->withErrors([
                'login' => "Ushbu hisob faolsizlantirilgan. Administrator bilan bog'laning.",
            ])->onlyInput('login');
        }

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            // Failsafe: only accept 'admin123' when demo mode or local/testing environment is active
            if ($allowDemoFailsafe && $isDemoAccount && $credentials['password'] === 'admin123') {
                $user->update([
                    'password' => Hash::make('admin123'),
                ]);
            } else {
                return back()->withErrors([
                    'login' => "Login yoki parol noto'g'ri.",
                ])->onlyInput('login');
            }
        }

        Auth::login($user);

        // Security requirement: regenerate session ID upon login to prevent session fixation
        $request->session()->regenerate();

        AuditService::log('web.login', $user);

        if ($user->hasRole(['superadmin', 'admin', 'moderator'])) {
            return redirect()->intended('/admin');
        }

        if ($user->hasRole(['seller_owner', 'seller_manager', 'seller_operator'])) {
            return redirect()->intended('/profile?tab=seller_products');
        }

        if ($user->hasRole(['courier', 'logistics_operator'])) {
            return redirect()->intended('/profile?tab=courier_shipments');
        }

        return redirect()->intended('/profile');
    }

    /**
     * Show the web registration form (customer, wholesale customer, or seller).
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('profile');
        }

        return view('auth.register');
    }

    /**
     * Register a new account from the web storefront and sign the user in.
     * Role is derived only from the whitelisted account_type, never from raw input.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'phone' => $request->filled('phone') ? preg_replace('/[^\d+]/', '', (string) $request->input('phone')) : null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+?998\d{9}$/', 'unique:users,phone'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'account_type' => ['required', 'string', Rule::in(['customer', 'wholesale_customer', 'seller'])],
            'store_name' => ['required_if:account_type,seller', 'nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'terms' => ['accepted'],
        ], [
            'phone.regex' => "Telefon raqami +998XXXXXXXXX formatida bo'lishi kerak.",
            'phone.unique' => "Bu telefon raqami allaqachon ro'yxatdan o'tgan. Tizimga kiring.",
            'email.unique' => "Bu email allaqachon ro'yxatdan o'tgan.",
            'password.confirmed' => 'Parollar mos kelmadi.',
            'store_name.required_if' => "Do'kon nomini kiriting.",
            'terms.accepted' => 'Foydalanish shartlariga rozilik bildiring.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? (ltrim($validated['phone'], '+') . '@placeholder.local'),
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'is_active' => true,
            ]);

            if ($validated['account_type'] === 'seller') {
                $seller = Seller::create([
                    'name' => $validated['store_name'],
                    'slug' => Str::slug($validated['store_name']) . '-' . Str::lower(Str::random(5)),
                    'tax_id' => $validated['tax_id'] ?? null,
                    'status' => 'pending', // Requires admin approval before products go live
                    'phone' => $user->phone,
                    'email' => $user->email,
                ]);

                SellerMember::create([
                    'seller_id' => $seller->id,
                    'user_id' => $user->id,
                    'role' => 'owner',
                    'permissions' => ['*'],
                    'is_active' => true,
                ]);

                $user->assignRole('seller_owner');
            } else {
                $user->assignRole($validated['account_type']);
            }

            return $user;
        });

        AuditService::log('web.registered', $user, null, ['account_type' => $validated['account_type']]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($validated['account_type'] === 'seller') {
            return redirect('/profile?tab=seller_products')
                ->with('success', "Do'koningiz yaratildi va moderatsiyaga yuborildi. Tasdiqlangach mahsulotlaringiz saytda ko'rinadi.");
        }

        return redirect()->intended('/profile')->with('success', "Xush kelibsiz! Hisobingiz muvaffaqiyatli yaratildi.");
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
