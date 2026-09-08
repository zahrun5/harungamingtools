<?php

namespace App\Console\Commands;

use App\Jobs\ScanFlipOpportunities;
use App\Models\Category;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('flip:scan-all')]
#[Description('Dispatch flip scan jobs for all leaf categories (bertahap dengan delay)')]
class DispatchFlipScans extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ambil semua leaf categories (kategori yang tidak punya children)
        $leafCategories = Category::whereDoesntHave('children')->get();
        
        $this->info("Found {$leafCategories->count()} leaf categories to scan.");
        
        $delaySeconds = 0;
        
        foreach ($leafCategories as $category) {
            // Dispatch job dengan delay bertahap (setiap 2 menit)
            ScanFlipOpportunities::dispatch($category->id)
                ->delay(now()->addSeconds($delaySeconds));
            
            $this->line("Dispatched: {$category->name} (ID: {$category->id}) - delay: {$delaySeconds}s");
            
            $delaySeconds += 120; // 2 menit per kategori
        }
        
        $totalMinutes = ceil($delaySeconds / 60);
        $this->info("All jobs dispatched! Total estimated time: ~{$totalMinutes} minutes");
        
        return Command::SUCCESS;
    }
}
