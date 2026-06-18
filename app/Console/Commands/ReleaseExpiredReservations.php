<?php

namespace App\Console\Commands;

use App\Models\StockReservation;
use Illuminate\Console\Command;

class ReleaseExpiredReservations extends Command
{
    /**
     * Command name
     */
    protected $signature =
        'app:release-expired-reservations';

    /**
     * Command description
     */
    protected $description =
        'Release expired stock reservations';

    /**
     * Execute command
     */
    public function handle()
    {
        $released = StockReservation::query()

            ->where(
                'status',
                'active'
            )

            ->where(
                'expires_at',
                '<',
                now()
            )

            ->update([
                'status' => 'released'
            ]);

        $this->info(
            "Released {$released} expired reservations."
        );

        return Command::SUCCESS;
    }
}