<?php

namespace App\Http\Middleware;

use App\Models\Seller;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnsureSellerTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(Response::HTTP_UNAUTHORIZED, "Autentifikatsiyadan o'tilmagan");
        }

        // 1. Determine target seller ID from route parameter, if present
        $sellerParam = $request->route('seller') ?? $request->route('sellerId') ?? $request->route('id');
        $targetSellerId = null;

        if ($sellerParam instanceof Seller) {
            $targetSellerId = $sellerParam->id;
        } elseif (is_numeric($sellerParam)) {
            $targetSellerId = (int) $sellerParam;
        }

        // 2. If no specific seller in route, use user's active seller
        if (!$targetSellerId) {
            $seller = $user->activeSeller();
            if (!$seller) {
                throw new AccessDeniedHttpException("Siz biron bir do'konga a'zo emassiz");
            }
            $targetSellerId = $seller->id;
        } else {
            $seller = Seller::find($targetSellerId);
            if (!$seller) {
                throw new NotFoundHttpException("Bunday do'kon topilmadi");
            }
        }

        // 3. Platform Admin can access any seller
        if ($user->hasRole('admin')) {
            $request->attributes->set('current_seller', $seller);
            return $next($request);
        }

        // 4. Strict tenant isolation check: User must be an active member of THIS seller
        if (!$user->belongsToSeller($targetSellerId)) {
            throw new AccessDeniedHttpException("Siz boshqa sotuvchining ma'lumotlarini ko'ra yoki o'zgartira olmaysiz");
        }

        // Check if seller account is suspended or rejected
        if ($seller->status === 'suspended') {
            throw new AccessDeniedHttpException("Ushbu do'kon faoliyati to'xtatilgan");
        }

        $request->attributes->set('current_seller', $seller);

        return $next($request);
    }
}
