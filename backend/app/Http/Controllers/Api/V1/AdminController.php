<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * List users with pagination and search.
     */
    public function users(Request $request): JsonResponse
    {
        $query = User::with('roles')->orderByDesc('id');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        $users = $query->paginate(20);

        return ApiResponse::success($users);
    }

    /**
     * List all sellers with filters.
     */
    public function sellers(Request $request): JsonResponse
    {
        $query = Seller::withCount('members')->orderByDesc('id');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('legal_name', 'like', "%{$search}%")
                  ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        $sellers = $query->paginate(20);

        return ApiResponse::success($sellers);
    }

    /**
     * Update seller status (e.g. pending -> active / suspended / rejected).
     */
    public function updateSellerStatus(Request $request, int $id): JsonResponse
    {
        $seller = Seller::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'suspended', 'rejected'])],
            'commission_rate' => ['nullable', 'numeric', 'between:0,100'],
        ]);

        $oldValues = $seller->only(['status', 'commission_rate']);
        $seller->update($validated);

        AuditService::log('seller.status_updated', $seller, $oldValues, $validated);

        return ApiResponse::success($seller, "Sotuvchi holati o'zgartirildi: {$seller->status}");
    }

    /**
     * Activate or deactivate a user account.
     */
    public function updateUserStatus(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $oldValues = ['is_active' => $user->is_active];
        $user->update(['is_active' => $validated['is_active']]);

        AuditService::log('user.status_updated', $user, $oldValues, $validated);

        $statusText = $validated['is_active'] ? 'faollashtirildi' : 'faolsizlantirildi';

        return ApiResponse::success($user, "Foydalanuvchi hisobi {$statusText}");
    }
}
