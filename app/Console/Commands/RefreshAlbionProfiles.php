<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AlbionApiService;
use Illuminate\Console\Command;

class RefreshAlbionProfiles extends Command
{
    /**
     * php artisan albion:refresh-profiles
     */
    protected $signature = 'albion:refresh-profiles';

    protected $description = 'Refresh data Albion (guild, alliance, fame, avatar, dll) untuk semua user yang sudah verified';

    public function handle(AlbionApiService $albionApi): int
    {
        $users = User::where('verification_status', 'verified')
            ->whereNotNull('albion_player_id')
            ->get();

        $this->info("Refresh data buat {$users->count()} user verified...");

        $success = 0;
        $failed = 0;

        foreach ($users as $user) {
            $detail = $albionApi->getPlayerDetail($user->albion_player_id, $user->albion_server);

            if (! $detail) {
                $this->warn("  ✗ Gagal ambil data buat {$user->albion_ign} (server {$user->albion_server})");
                $failed++;
                continue;
            }

            $user->update(array_merge(
                $albionApi->mapToUserAttributes($detail),
                ['albion_data_refreshed_at' => now()]
            ));

            $this->line("  ✓ {$user->albion_ign} diperbarui");
            $success++;
        }

        $this->info("Selesai: {$success} berhasil, {$failed} gagal.");

        return self::SUCCESS;
    }
}
