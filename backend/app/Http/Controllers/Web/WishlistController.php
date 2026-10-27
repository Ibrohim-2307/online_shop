<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    /**
     * Display the wishlist page with real product cards.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user) {
            $products = $user->wishlistProducts()
                ->where('is_active', true)
                ->where('status', 'approved')
                ->with(['category:id,name,slug', 'seller:id,name,slug,rating', 'variants.priceTiers'])
                ->paginate(16);
        } else {
            // For guests, check cookie or query IDs
            $cookieIds = json_decode($request->cookie('guest_wishlist', '[]'), true) ?: [];
            if (!empty($cookieIds)) {
                $products = Product::whereIn('id', $cookieIds)
                    ->where('is_active', true)
                    ->where('status', 'approved')
                    ->with(['category:id,name,slug', 'seller:id,name,slug,rating', 'variants.priceTiers'])
                    ->paginate(16);
            } else {
                $products = Product::whereRaw('1 = 0')->paginate(16);
            }
        }

        return view('storefront.wishlist', compact('products'));
    }

    /**
     * Toggle a product in user's wishlist (AJAX or form POST).
     */
    public function toggle(Request $request, int $productId): JsonResponse|RedirectResponse
    {
        $product = Product::findOrFail($productId);
        $user = $request->user();

        if ($user) {
            $existing = Wishlist::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existing) {
                $existing->delete();
                $status = 'removed';
                $message = "Mahsulot sevimlilardan olib tashlandi.";
            } else {
                Wishlist::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
                $status = 'added';
                $message = "Mahsulot sevimlilarga qo'shildi!";
            }

            $count = Wishlist::where('user_id', $user->id)->count();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'status' => $status,
                    'count' => $count,
                    'message' => $message,
                ]);
            }

            return back()->with('success', $message);
        }

        // Guest handling
        $guestIds = json_decode($request->cookie('guest_wishlist', '[]'), true) ?: [];
        if (in_array($product->id, $guestIds)) {
            $guestIds = array_values(array_diff($guestIds, [$product->id]));
            $status = 'removed';
            $message = "Mahsulot sevimlilardan olib tashlandi.";
        } else {
            $guestIds[] = $product->id;
            $status = 'added';
            $message = "Mahsulot sevimlilarga qo'shildi!";
        }

        $cookie = cookie('guest_wishlist', json_encode(array_values(array_unique($guestIds))), 60 * 24 * 30);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'count' => count($guestIds),
                'message' => $message,
            ])->withCookie($cookie);
        }

        return back()->with('success', $message)->withCookie($cookie);
    }

    /**
     * Synchronize client localStorage items with database upon login.
     */
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Foydalanuvchi tizimga kirmagan.'], 401);
        }

        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        foreach ($validated['product_ids'] as $productId) {
            Wishlist::firstOrCreate([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
        }

        $count = Wishlist::where('user_id', $user->id)->count();
        $allIds = Wishlist::where('user_id', $user->id)->pluck('product_id')->toArray();

        return response()->json([
            'success' => true,
            'count' => $count,
            'product_ids' => $allIds,
            'message' => 'Sevimlilar hisobingiz bilan muvaffaqiyatli sinxronlashtirildi.',
        ]);
    }
}
