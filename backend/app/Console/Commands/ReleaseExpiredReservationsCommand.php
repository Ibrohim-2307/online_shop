<?php

namespace App\Console\Commands;

use App\Services\Checkout\InventoryReservationService;
use Illuminate\Console\Command;

class ReleaseExpiredReservationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muddati o\'tgan to\'lanmagan tovar bandlarini (reservations) avtomatik bo\'shatish';

    /**
     * Execute the console command.
     */
    public function handle(InventoryReservationService $reservationService): int
    {
        $this->info("Muddati o'tgan ombor bandlari tekshirilmoqda...");

        $releasedCount = $reservationService->releaseExpiredReservations();

        if ($releasedCount > 0) {
            $this->info("Muvaffaqiyatli: {$releasedCount} ta band bo'shatildi va zaxiraga qaytarildi.");
        } else {
            $this->line("Bo'shatiladigan muddati o'tgan bandlar mavjud emas.");
        }

        return self::SUCCESS;
    }
}
