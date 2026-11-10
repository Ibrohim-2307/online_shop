<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerLedger;
use App\Models\SellerMember;
use App\Models\SellerPayout;
use App\Models\User;
use App\Services\Ledger\SellerLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerPayoutWebTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $roleName, ?Seller $seller = null, string $memberRole = 'owner'): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => ucfirst($roleName)]);

        $user = User::create([
            'name' => ucfirst($roleName) . ' ' . uniqid(),
            'email' => uniqid() . '@example.uz',
            'phone' => '+99890' . random_int(1000000, 9999999),
            'password' => bcrypt('Secret123!'),
            'is_active' => true,
        ]);
        $user->roles()->attach($role->id);

        if ($seller) {
            SellerMember::create(['seller_id' => $seller->id, 'user_id' => $user->id, 'role' => $memberRole, 'is_active' => true]);
            $user->update(['seller_id' => $seller->id]);
        }

        return $user->fresh();
    }

    protected function seller(): Seller
    {
        return Seller::create([
            'name' => 'Sinov Do\'koni',
            'slug' => 'sinov-' . uniqid(),
            'legal_name' => 'Sinov MCHJ',
            'tax_id' => '30' . random_int(1000000, 9999999),
            'status' => 'active',
            'commission_rate' => 5.0,
            'rating' => 4.8,
        ]);
    }

    /** A sale of $gross with 5% commission, made $daysAgo days ago. */
    protected function sale(Seller $seller, int $gross, int $daysAgo): void
    {
        $at = now()->subDays($daysAgo);
        SellerLedger::create(['seller_id' => $seller->id, 'type' => 'credit', 'entry_type' => 'order_sale', 'amount' => $gross, 'currency' => 'UZS', 'running_balance' => 0, 'created_at' => $at]);
        SellerLedger::create(['seller_id' => $seller->id, 'type' => 'debit', 'entry_type' => 'commission_fee', 'amount' => (int) ($gross * 0.05), 'currency' => 'UZS', 'running_balance' => 0, 'created_at' => $at]);
    }

    protected function payoutForm(array $overrides = []): array
    {
        return array_merge([
            'amount' => '500 000',
            'method' => 'card',
            'account_number' => '8600 1234 5678 9012',
            'holder_name' => 'Aliyev Vali',
        ], $overrides);
    }

    public function test_summary_separates_held_pending_and_available_money(): void
    {
        $seller = $this->seller();
        $this->sale($seller, 1000000, 10); // 950 000 free
        $this->sale($seller, 200000, 1);   // 190 000 still on hold
        SellerPayout::create(['seller_id' => $seller->id, 'amount' => 300000, 'status' => 'requested']);

        $summary = app(SellerLedgerService::class)->summary($seller);

        $this->assertSame(1140000, $summary['balance']);
        $this->assertSame(190000, $summary['on_hold']);
        $this->assertSame(300000, $summary['pending_payouts']);
        $this->assertSame(650000, $summary['available']);
        $this->assertSame(1200000, $summary['total_sales']);
        $this->assertSame(60000, $summary['total_commission']);
    }

    public function test_owner_requests_payout_and_sees_it_on_finance_page(): void
    {
        $seller = $this->seller();
        $owner = $this->user('seller_owner', $seller);
        $this->sale($seller, 1000000, 10);

        $this->actingAs($owner)->post(route('profile.seller.payouts.store'), $this->payoutForm())
            ->assertRedirect(route('profile', ['tab' => 'seller_finance']))
            ->assertSessionHas('success');

        $payout = SellerPayout::firstOrFail();
        $this->assertSame(500000, $payout->amount);
        $this->assertSame('requested', $payout->status);
        $this->assertSame('8600123456789012', $payout->bank_details['number']);

        // Asking does not move money yet
        $this->assertSame(950000, app(SellerLedgerService::class)->getSellerBalance($seller));

        $page = $this->actingAs($owner)->get(route('profile', ['tab' => 'seller_finance']))->assertOk();
        $page->assertSee('Yechish mumkin');
        $page->assertSee('450 000');
        $page->assertSee('•••• 9012');
        $page->assertDontSee('8600123456789012');
    }

    public function test_the_same_money_cannot_be_requested_twice(): void
    {
        $seller = $this->seller();
        $owner = $this->user('seller_owner', $seller);
        $this->sale($seller, 1000000, 10);

        $this->actingAs($owner)->post(route('profile.seller.payouts.store'), $this->payoutForm(['amount' => '900000']));
        $this->actingAs($owner)->post(route('profile.seller.payouts.store'), $this->payoutForm(['amount' => '900000']))
            ->assertSessionHas('error');

        $this->assertSame(1, SellerPayout::count());
    }

    public function test_money_on_hold_cannot_be_requested(): void
    {
        $seller = $this->seller();
        $owner = $this->user('seller_owner', $seller);
        $this->sale($seller, 1000000, 1);

        $this->actingAs($owner)->post(route('profile.seller.payouts.store'), $this->payoutForm())
            ->assertSessionHas('error');

        $this->assertSame(0, SellerPayout::count());
    }

    public function test_wrong_card_number_is_rejected(): void
    {
        $seller = $this->seller();
        $owner = $this->user('seller_owner', $seller);
        $this->sale($seller, 1000000, 10);

        $this->actingAs($owner)->post(route('profile.seller.payouts.store'), $this->payoutForm(['account_number' => '8600 1234']))
            ->assertSessionHasErrors('account_number');

        $this->actingAs($owner)->post(route('profile.seller.payouts.store'), $this->payoutForm(['amount' => '5000']))
            ->assertSessionHasErrors('amount');

        $this->assertSame(0, SellerPayout::count());
    }

    public function test_store_staff_and_strangers_cannot_request_payout(): void
    {
        $seller = $this->seller();
        $this->sale($seller, 1000000, 10);

        $operator = $this->user('seller_operator', $seller, 'operator');
        $this->actingAs($operator)->post(route('profile.seller.payouts.store'), $this->payoutForm())->assertForbidden();

        $customer = $this->user('customer');
        $this->actingAs($customer)->post(route('profile.seller.payouts.store'), $this->payoutForm())->assertForbidden();

        $this->assertSame(0, SellerPayout::count());
    }

    public function test_admin_marks_payout_paid_once_and_ledger_is_debited_once(): void
    {
        $seller = $this->seller();
        $this->sale($seller, 1000000, 10);
        $admin = $this->user('admin');
        $payout = SellerPayout::create(['seller_id' => $seller->id, 'amount' => 500000, 'status' => 'requested', 'bank_details' => ['method' => 'card', 'number' => '8600123456789012', 'holder' => 'Aliyev Vali']]);

        $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'payouts']))
            ->assertOk()
            ->assertSee('8600 1234 5678 9012');

        $this->actingAs($admin)->post(route('admin.payouts.review', $payout->id), ['decision' => 'paid'])
            ->assertSessionHas('success');
        // A second click must not take the money twice
        $this->actingAs($admin)->post(route('admin.payouts.review', $payout->id), ['decision' => 'paid'])
            ->assertSessionHas('error');

        $this->assertSame('completed', $payout->fresh()->status);
        $this->assertSame($admin->id, $payout->fresh()->reviewed_by);
        $this->assertSame(1, SellerLedger::where('entry_type', 'payout')->count());
        $this->assertSame(450000, app(SellerLedgerService::class)->getSellerBalance($seller));
    }

    public function test_rejecting_needs_a_reason_and_leaves_the_money(): void
    {
        $seller = $this->seller();
        $owner = $this->user('seller_owner', $seller);
        $this->sale($seller, 1000000, 10);
        $admin = $this->user('admin');
        $payout = SellerPayout::create(['seller_id' => $seller->id, 'amount' => 500000, 'status' => 'requested', 'bank_details' => ['method' => 'card', 'number' => '8600123456789012', 'holder' => 'Aliyev Vali']]);

        $this->actingAs($admin)->post(route('admin.payouts.review', $payout->id), ['decision' => 'reject'])
            ->assertSessionHasErrors('notes');

        $this->actingAs($admin)->post(route('admin.payouts.review', $payout->id), ['decision' => 'reject', 'notes' => 'Karta raqami xato'])
            ->assertSessionHas('success');

        $this->assertSame('rejected', $payout->fresh()->status);
        $this->assertSame(0, SellerLedger::where('entry_type', 'payout')->count());
        $this->assertSame(950000, app(SellerLedgerService::class)->summary($seller)['available']);

        $this->actingAs($owner)->get(route('profile', ['tab' => 'seller_finance']))
            ->assertOk()
            ->assertSee('Karta raqami xato');
    }

    public function test_payout_larger_than_balance_cannot_be_paid(): void
    {
        $seller = $this->seller();
        $this->sale($seller, 100000, 10);
        $admin = $this->user('admin');
        $payout = SellerPayout::create(['seller_id' => $seller->id, 'amount' => 500000, 'status' => 'requested']);

        $this->actingAs($admin)->post(route('admin.payouts.review', $payout->id), ['decision' => 'paid'])
            ->assertSessionHas('error');

        $this->assertSame('requested', $payout->fresh()->status);
        $this->assertSame(0, SellerLedger::where('entry_type', 'payout')->count());
    }

    public function test_admin_sees_platform_finance_and_moderator_does_not(): void
    {
        $seller = $this->seller();
        $this->sale($seller, 1000000, 40);
        $this->sale($seller, 200000, 1);
        $admin = $this->user('admin');
        $payout = SellerPayout::create(['seller_id' => $seller->id, 'amount' => 300000, 'status' => 'requested']);
        app(SellerLedgerService::class)->reviewPayout($payout, 'completed', null, $admin);

        $finance = app(SellerLedgerService::class)->platformSummary(30);
        $this->assertSame(1200000, $finance['all']['sales']);
        $this->assertSame(60000, $finance['all']['commission']);
        $this->assertSame(10000, $finance['recent']['commission']);
        $this->assertSame(300000, $finance['all']['paid_out']);
        $this->assertSame(840000, $finance['owed_to_sellers']);
        $this->assertSame(840000, $finance['sellers'][0]['balance']);

        $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'finance']))
            ->assertOk()
            ->assertSee('Sayt daromadi')
            ->assertSee('840 000');

        $this->actingAs($this->user('moderator'))->get(route('admin.dashboard', ['tab' => 'finance']))
            ->assertOk()
            ->assertDontSee('Sayt daromadi');
    }

    public function test_moderator_cannot_pay_or_see_payouts(): void
    {
        $seller = $this->seller();
        $this->sale($seller, 1000000, 10);
        $moderator = $this->user('moderator');
        $payout = SellerPayout::create(['seller_id' => $seller->id, 'amount' => 500000, 'status' => 'requested', 'bank_details' => ['method' => 'card', 'number' => '8600123456789012']]);

        $this->actingAs($moderator)->post(route('admin.payouts.review', $payout->id), ['decision' => 'paid'])->assertForbidden();
        $this->actingAs($moderator)->get(route('admin.dashboard', ['tab' => 'payouts']))
            ->assertOk()
            ->assertDontSee('8600 1234 5678 9012');

        $this->assertSame('requested', $payout->fresh()->status);
    }
}
