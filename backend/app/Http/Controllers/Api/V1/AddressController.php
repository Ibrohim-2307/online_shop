<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AddressController extends Controller
{
    /**
     * Display a listing of user addresses.
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return ApiResponse::success($addresses);
    }

    /**
     * Store a newly created address.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'full_address' => ['required', 'string'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();

        if (!empty($validated['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        } else {
            // If it's user's first address, make it default automatically
            if ($user->addresses()->count() === 0) {
                $validated['is_default'] = true;
            }
        }

        $address = $user->addresses()->create($validated);

        return ApiResponse::created($address, "Manzil muvaffaqiyatli saqlandi");
    }

    /**
     * Display the specified address.
     */
    public function show(Request $request, Address $address): JsonResponse
    {
        if ($request->user()->cannot('view', $address)) {
            throw new AccessDeniedHttpException("Ushbu manzil sizning hisobingizga tegishli emas");
        }

        return ApiResponse::success($address);
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request, Address $address): JsonResponse
    {
        if ($request->user()->cannot('update', $address)) {
            throw new AccessDeniedHttpException("Ushbu manzil sizning hisobingizga tegishli emas");
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:50'],
            'recipient_name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'full_address' => ['sometimes', 'required', 'string'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return ApiResponse::success($address, "Manzil yangilandi");
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($request->user()->cannot('delete', $address)) {
            throw new AccessDeniedHttpException("Ushbu manzil sizning hisobingizga tegishli emas");
        }

        $address->delete();

        return ApiResponse::success(null, "Manzil muvaffaqiyatli o'chirildi");
    }
}
