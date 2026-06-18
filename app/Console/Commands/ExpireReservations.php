<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockReservation;

class ExpireReservations extends Command
{
    protected $signature = 'reservation:expire';

    protected $description = 'Release expired stock reservations';

    public function handle()
    {
        $count = StockReservation::query()
            ->where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => 'released'
            ]);

        $this->info("Released {$count} expired reservations");

        return self::SUCCESS;
    }
}