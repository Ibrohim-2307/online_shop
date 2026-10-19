<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SellerMember;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SellerController extends Controller
{
    /**
     * Resolve the target seller and verify tenant access.
     */
    protected function resolveSeller(Request $request, ?int $sellerId = null): Seller
    {
        $user = $request->user();
        $targetSellerId = $sellerId ?? ($request->route('sellerId') ? (int) $request->route('sellerId') : null);

        if ($targetSellerId) {
            $seller = Seller::find($targetSellerId);
            if (!$seller) {
                throw new NotFoundHttpException("Bunday do'kon topilmadi");
            }

            if ($user->cannot('view', $seller)) {
                throw new AccessDeniedHttpException("Siz boshqa do'kon ma'lumotlarini ko'ra olmaysiz");
            }

            return $seller;
        }

        $seller = $request->attributes->get('current_seller') ?? $user->activeSeller();

        if (!$seller) {
            throw new AccessDeniedHttpException("Siz biron bir do'konga a'zo emassiz");
        }

        return $seller;
    }

    /**
     * View store profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $seller = $this->resolveSeller($request);

        return ApiResponse::success([
            'id' => $seller->id,
            'name' => $seller->name,
            'slug' => $seller->slug,
            'legal_name' => $seller->legal_name,
            'tax_id' => $seller->tax_id,
            'status' => $seller->status,
            'phone' => $seller->phone,
            'email' => $seller->email,
            'address' => $seller->address,
            'commission_rate' => $seller->commission_rate,
            'rating' => $seller->rating,
            'created_at' => $seller->created_at?->toIso8601String(),
        ]);
    }

    /**
     * Update store profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $seller = $this->resolveSeller($request);

        if ($request->user()->cannot('update', $seller)) {
            throw new AccessDeniedHttpException("Do'kon ma'lumotlarini o'zgartirish uchun sizda ruxsat yo'q");
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
        ]);

        $seller->update($validated);

        return ApiResponse::success($seller, "Do'kon ma'lumotlari muvaffaqiyatli yangilandi");
    }

    /**
     * List members/staff of the store.
     */
    public function members(Request $request): JsonResponse
    {
        $seller = $this->resolveSeller($request);

        if ($request->user()->cannot('manageMembers', $seller)) {
            throw new AccessDeniedHttpException("Do'kon xodimlarini ko'rish uchun sizda ruxsat yo'q");
        }

        $members = $seller->members()->with('user:id,name,email,phone')->get();

        return ApiResponse::success($members);
    }

    /**
     * Add a staff member to the store.
     */
    public function addMember(Request $request): JsonResponse
    {
        $seller = $this->resolveSeller($request);

        if ($request->user()->cannot('manageMembers', $seller)) {
            throw new AccessDeniedHttpException("Do'kon xodimlarini qo'shish uchun sizda ruxsat yo'q");
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', 'string', Rule::in(['manager', 'operator', 'accountant'])],
            'permissions' => ['nullable', 'array'],
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        // Check if user is already a member
        if ($seller->members()->where('user_id', $targetUser->id)->exists()) {
            return ApiResponse::error(
                message: "Ushbu foydalanuvchi allaqachon do'kon xodimi hisoblanadi",
                code: 'MEMBER_EXISTS',
                status: 422
            );
        }

        $member = $seller->members()->create([
            'user_id' => $targetUser->id,
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? [],
            'is_active' => true,
        ]);

        // Assign appropriate role
        $roleName = 'seller_' . $validated['role'];
        $targetUser->assignRole($roleName);

        return ApiResponse::created($member->load('user:id,name,email,phone'), "Xodim do'konga muvaffaqiyatli biriktirildi");
    }

    /**
     * Remove a staff member from the store.
     */
    public function removeMember(Request $request): JsonResponse
    {
        $seller = $this->resolveSeller($request);

        if ($request->user()->cannot('manageMembers', $seller)) {
            throw new AccessDeniedHttpException("Do'kon xodimlarini boshqarish uchun sizda ruxsat yo'q");
        }

        $memberId = (int) $request->route('memberId');
        $member = SellerMember::find($memberId);

        if (!$member) {
            throw new NotFoundHttpException("Xodim topilmadi");
        }

        // Strict cross-seller isolation: cannot remove member of another seller!
        if ($member->seller_id !== $seller->id) {
            throw new AccessDeniedHttpException("Ushbu xodim sizning do'koningizga tegishli emas");
        }

        if ($member->isOwner()) {
            return ApiResponse::forbidden("Do'kon egasini o'chirib bo'lmaydi");
        }

        $user = $member->user;
        $member->delete();

        // Remove seller role if not in any other seller
        if ($user && $user->sellerMemberships()->count() === 0) {
            $user->removeRole('seller_manager');
            $user->removeRole('seller_operator');
        }

        return ApiResponse::success(null, "Xodim do'kondan muvaffaqiyatli chiqarildi");
    }
}
