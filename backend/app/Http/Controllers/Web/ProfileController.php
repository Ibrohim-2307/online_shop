<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Customer Profile & Account Center (Amazon / AliExpress / Uzum style).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // If not logged in, provide active demo view as Dilshod Xaridor so the customer experience is immediately tangible
        $isDemo = false;
        if (!$user) {
            $user = User::where('email', 'customer@market.uz')->first()
                 ?? User::first();
            $isDemo = true;
        }

        $activeTab = $request->query('tab', 'orders'); // 'orders', 'wishlist', 'addresses', 'settings'

        // 1. Customer Orders
        $orders = Order::where('user_id', $user->id)
            ->with([
                'suborders.seller',
                'suborders.items',
                'suborders.shipments.courier',
            ])
            ->latest()
            ->get();

        $activeOrdersCount = $orders->filter(fn($o) => 
            $o->status === 'processing' || 
            $o->suborders->flatMap->shipments->contains(fn($s) => in_array($s->status, ['in_transit', 'out_for_delivery']))
        )->count();

        // 2. Saved Addresses
        $addresses = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        // 3. Saved / Curated Products for Wishlist Tab
        $savedProducts = Product::where('status', 'approved')
            ->where('is_active', true)
            ->with(['seller:id,name,slug,rating', 'category:id,name,slug', 'variants.priceTiers'])
            ->take(6)
            ->get();

        return view('storefront.profile', compact(
            'user',
            'isDemo',
            'activeTab',
            'orders',
            'activeOrdersCount',
            'addresses',
            'savedProducts'
        ));
    }

    /**
     * Update customer personal profile (Name, Phone, Email).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Iltimos, tizimga kiring.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil ma\'lumotlari muvaffaqiyatli saqlandi.');
    }

    /**
     * Update customer account password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Iltimos, tizimga kiring.');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Joriy parol noto\'g\'ri kiritildi.']);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('success', 'Parolingiz muvaffaqiyatli yangilandi.');
    }

    /**
     * Add new delivery address.
     */
    public function storeAddress(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            $user = User::where('email', 'customer@market.uz')->first();
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25'],
            'full_address' => ['required', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = $request->has('is_default');

        if ($isDefault) {
            Address::where('user_id', $user->id)->update(['is_default' => false]);
        }

        Address::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'recipient_name' => $validated['recipient_name'],
            'phone' => $validated['phone'],
            'full_address' => $validated['full_address'],
            'landmark' => $validated['landmark'] ?? null,
            'is_default' => $isDefault || Address::where('user_id', $user->id)->count() === 0,
        ]);

        return back()->with('success', 'Yangi yetkazib berish manzili qo\'shildi.');
    }

    /**
     * Delete an existing address.
     */
    public function deleteAddress(int $id): RedirectResponse
    {
        $user = Auth::user();
        $userId = $user ? $user->id : User::where('email', 'customer@market.uz')->value('id');

        $address = Address::where('id', $id)->where('user_id', $userId)->firstOrFail();
        $address->delete();

        return back()->with('success', 'Manzil muvaffaqiyatli o\'chirildi.');
    }

    /**
     * Set default address.
     */
    public function setDefaultAddress(int $id): RedirectResponse
    {
        $user = Auth::user();
        $userId = $user ? $user->id : User::where('email', 'customer@market.uz')->value('id');

        Address::where('user_id', $userId)->update(['is_default' => false]);
        Address::where('id', $id)->where('user_id', $userId)->update(['is_default' => true]);

        return back()->with('success', 'Asosiy yetkazib berish manzili belgilandi.');
    }
}
