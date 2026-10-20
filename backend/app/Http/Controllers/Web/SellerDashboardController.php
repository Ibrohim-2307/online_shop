<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\Seller;
use App\Models\SellerLedger;
use App\Models\Suborder;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    /**
     * Display the Seller Dashboard web page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['seller_owner', 'seller_manager', 'seller_operator'])) {
            return redirect()->route('login')->with('error', 'Sotuvchi paneliga kirish uchun do\'kon xodimi yoki egasi hisobi talab qilinadi.');
        }

        $sellerId = $user->seller_id;
        $seller = Seller::find($sellerId);
        if (!$seller) {
            // Fallback for first demo seller if membership query differs
            $seller = Seller::first();
        }

        $products = Product::with(['variants.priceTiers', 'variants.inventoryBalances.warehouse'])
            ->where('seller_id', $seller->id)
            ->latest()
            ->get();

        $suborders = Suborder::with(['order', 'items', 'shipment'])
            ->where('seller_id', $seller->id)
            ->latest()
            ->limit(15)
            ->get();

        $warehouses = Warehouse::with('balances.variant')
            ->where('seller_id', $seller->id)
            ->get();

        $rfqs = Rfq::with('quotations')
            ->latest()
            ->limit(10)
            ->get();

        // Calculate ledger balance: credits - debits
        $ledgerEntries = SellerLedger::where('seller_id', $seller->id)->latest()->limit(15)->get();
        $totalCredit = SellerLedger::where('seller_id', $seller->id)->where('type', 'credit')->sum('amount');
        $totalDebit = SellerLedger::where('seller_id', $seller->id)->where('type', 'debit')->sum('amount');
        $currentBalance = $totalCredit - $totalDebit;

        $stats = [
            'total_products' => $products->count(),
            'total_suborders' => Suborder::where('seller_id', $seller->id)->count(),
            'pending_orders' => Suborder::where('seller_id', $seller->id)->where('status', 'pending')->count(),
            'current_balance' => $currentBalance,
            'rating' => $seller->rating,
        ];

        return view('seller.dashboard', compact('seller', 'products', 'suborders', 'warehouses', 'rfqs', 'ledgerEntries', 'stats'));
    }
}
