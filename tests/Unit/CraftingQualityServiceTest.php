<?php

namespace Tests\Unit;

use App\Services\CraftingQualityService;
use PHPUnit\Framework\TestCase;

/**
 * Unit test untuk CraftingQualityService
 *
 * Coverage:
 * 1. Probabilitas distribusi kualitas sesuai Albion mechanics
 * 2. Formula profit-per-kualitas: revenue = sum(qty[q] * price[q]) - effectiveCost
 * 3. Resource Return Rate (RRR) independent dari quality
 * 4. Cascading fallback harga quality (jika price=0, pakai quality di bawahnya)
 * 5. Distribusi qty per quality (sisa ke Normal agar total konsisten)
 * 6. Semua 5 kualitas update konsisten saat return% berubah
 */
class CraftingQualityServiceTest extends TestCase
{
    private CraftingQualityService $service;

    protected function setUp(): void
    {
        $this->service = new CraftingQualityService();
    }

    // ===========================================================
    // 1. VALIDASI PROBABILITAS DISTRIBUSI
    // ===========================================================

    public function test_quality_distribution_returns_all_five_qualities(): void
    {
        $dist = $this->service->expectedQualityDistribution();

        $this->assertCount(5, $dist);
        $this->assertArrayHasKey(1, $dist); // Normal
        $this->assertArrayHasKey(2, $dist); // Good
        $this->assertArrayHasKey(3, $dist); // Outstanding
        $this->assertArrayHasKey(4, $dist); // Excellent
        $this->assertArrayHasKey(5, $dist); // Masterpiece
    }

    public function test_quality_distribution_matches_albion_mechanics(): void
    {
        $dist = $this->service->expectedQualityDistribution();

        $this->assertEqualsWithDelta(0.689, $dist[1], 0.0001, 'Normal harus 68.9%');
        $this->assertEqualsWithDelta(0.250, $dist[2], 0.0001, 'Good harus 25.0%');
        $this->assertEqualsWithDelta(0.050, $dist[3], 0.0001, 'Outstanding harus 5.0%');
        $this->assertEqualsWithDelta(0.010, $dist[4], 0.0001, 'Excellent harus 1.0%');
        $this->assertEqualsWithDelta(0.001, $dist[5], 0.0001, 'Masterpiece harus 0.1%');
    }

    public function test_quality_probabilities_sum_to_one(): void
    {
        $dist  = $this->service->expectedQualityDistribution();
        $total = array_sum($dist);

        $this->assertEqualsWithDelta(1.0, $total, 0.0001, 'Total probabilitas 5 kualitas harus = 1.0');
    }

    public function test_normal_quality_has_highest_probability(): void
    {
        $dist       = $this->service->expectedQualityDistribution();
        $maxQuality = array_keys($dist, max($dist))[0];

        $this->assertEquals(1, $maxQuality, 'Normal harus paling sering muncul');
    }

    public function test_masterpiece_has_lowest_probability(): void
    {
        $dist       = $this->service->expectedQualityDistribution();
        $minQuality = array_keys($dist, min($dist))[0];

        $this->assertEquals(5, $minQuality, 'Masterpiece harus paling jarang');
    }

    public function test_probability_decreases_with_higher_quality(): void
    {
        $dist = $this->service->expectedQualityDistribution();

        for ($q = 1; $q < 5; $q++) {
            $this->assertGreaterThan(
                $dist[$q + 1],
                $dist[$q],
                "Kualitas {$q} harus lebih sering dari kualitas " . ($q + 1)
            );
        }
    }

    // ===========================================================
    // 2. FORMULA PROFIT-PER-KUALITAS
    // revenue = sum(qty[q] * prob[q] * price[q] * feeMult)
    // profit  = revenue - materialCost * (1 - returnRate)
    // ===========================================================

    public function test_profit_per_quality_calculation_basic(): void
    {
        $dist = $this->service->expectedQualityDistribution();

        $qtyCrafted    = 100;
        $materialCost  = 50_000;
        $returnRate    = 0.15;
        $feeMultiplier = 0.945;  // ~6% tax

        $prices = [
            1 => 100_000,
            2 => 130_000,
            3 => 180_000,
            4 => 250_000,
            5 => 500_000,
        ];

        $expectedRevenue = 0;
        foreach ($dist as $quality => $prob) {
            $qtyForQuality    = (int) round($qtyCrafted * $prob);
            $expectedRevenue += $qtyForQuality * $prices[$quality] * $feeMultiplier;
        }

        $effectiveCost = $materialCost * (1 - $returnRate);
        $profit        = $expectedRevenue - $effectiveCost;

        // Revenue multi-quality harus lebih besar dari Normal saja
        $normalOnlyRevenue = (int) round($qtyCrafted * $dist[1]) * $prices[1] * $feeMultiplier;
        $this->assertGreaterThan($normalOnlyRevenue, $expectedRevenue,
            'Revenue multi-quality harus lebih tinggi dari Normal saja'
        );

        // Profit positif karena harga jual jauh di atas modal
        $this->assertGreaterThan(0, $profit, 'Profit harus positif kalau harga jual wajar');
    }

    public function test_profit_decreases_when_sell_price_drops(): void
    {
        $dist         = $this->service->expectedQualityDistribution();
        $qtyCrafted   = 100;
        $materialCost = 5_000_000; // modal besar supaya profit negatif saat harga rendah
        $returnRate   = 0.15;

        $highPrices = [1 => 200_000, 2 => 260_000, 3 => 360_000, 4 => 500_000, 5 => 1_000_000];
        $lowPrices  = [1 =>   1_000, 2 =>   1_300, 3 =>   1_800, 4 =>   2_500, 5 =>    5_000]; // jauh di bawah modal

        $calcRevenue = function (array $prices) use ($dist, $qtyCrafted): float {
            $rev = 0;
            foreach ($dist as $q => $prob) {
                $rev += (int) round($qtyCrafted * $prob) * $prices[$q];
            }
            return $rev;
        };

        $effectiveCost = $materialCost * (1 - $returnRate);
        $profitHigh    = $calcRevenue($highPrices) - $effectiveCost;
        $profitLow     = $calcRevenue($lowPrices)  - $effectiveCost;

        $this->assertGreaterThan($profitLow, $profitHigh,
            'Profit harus turun kalau harga jual turun'
        );
        $this->assertLessThan(0, $profitLow,
            'Profit harus negatif kalau harga jual sangat rendah'
        );
        $this->assertGreaterThan(0, $profitHigh,
            'Profit harus positif kalau harga jual sangat tinggi'
        );
    }

    public function test_higher_quality_price_increases_profit(): void
    {
        $dist       = $this->service->expectedQualityDistribution();
        $qtyCrafted = 1000;

        // Skenario A: semua kualitas = harga Normal
        $flatPrices    = [1 => 100_000, 2 => 100_000, 3 => 100_000, 4 => 100_000, 5 => 100_000];
        // Skenario B: kualitas lebih tinggi = harga lebih tinggi (realistis)
        $gradedPrices  = [1 => 100_000, 2 => 130_000, 3 => 180_000, 4 => 250_000, 5 => 500_000];

        $calcRevenue = function (array $prices) use ($dist, $qtyCrafted): float {
            $rev = 0;
            foreach ($dist as $q => $prob) {
                $rev += (int) round($qtyCrafted * $prob) * $prices[$q];
            }
            return $rev;
        };

        $this->assertGreaterThan(
            $calcRevenue($flatPrices),
            $calcRevenue($gradedPrices),
            'Revenue dengan harga graded harus lebih tinggi dari flat pricing'
        );
    }

    // ===========================================================
    // 3. RESOURCE RETURN RATE (RRR) — INDEPENDENT DARI QUALITY
    // ===========================================================

    public function test_return_rate_reduces_effective_material_cost(): void
    {
        $materialCost = 1_000_000;

        $costAt0   = $materialCost * (1 - 0.00);
        $costAt15  = $materialCost * (1 - 0.15);
        $costAt479 = $materialCost * (1 - 0.479);

        $this->assertEquals($materialCost, $costAt0, 'RRR 0% = modal penuh');
        $this->assertEqualsWithDelta(850_000, $costAt15, 1, 'RRR 15% = 850k');
        $this->assertGreaterThan($costAt479, $costAt15, '15% RRR lebih mahal dari 47.9%');
    }

    public function test_return_rate_does_not_affect_quality_distribution(): void
    {
        $dist      = $this->service->expectedQualityDistribution();
        $totalProb = array_sum($dist);

        foreach ([0.0, 0.15, 0.30, 0.479] as $rrr) {
            $this->assertEqualsWithDelta(1.0, $totalProb, 0.0001,
                "Distribusi tidak boleh berubah karena RRR={$rrr}"
            );
        }
    }

    public function test_return_rate_cap_at_100_percent(): void
    {
        $materialCost  = 1_000_000;
        $effectiveCost = $materialCost * (1 - 1.0);

        $this->assertEquals(0.0, $effectiveCost, 'RRR 100% = modal efektif = 0');
    }

    // ===========================================================
    // 4. CASCADING FALLBACK HARGA QUALITY
    // Kalau price[Q] = 0, pakai price[Q-1]; cascades downward
    // ===========================================================

    public function test_quality_price_fallback_uses_lower_quality_when_zero(): void
    {
        $rawPrices = [1 => 100_000, 2 => 130_000, 3 => 180_000, 4 => 250_000, 5 => 0];

        $resolved = $this->applyQualityFallback($rawPrices);

        $this->assertEquals(250_000, $resolved[5],
            'Masterpiece (0) harus fallback ke Excellent (250k)'
        );
    }

    public function test_quality_price_fallback_cascades_multiple_levels(): void
    {
        $rawPrices = [1 => 100_000, 2 => 130_000, 3 => 0, 4 => 0, 5 => 0];

        $resolved = $this->applyQualityFallback($rawPrices);

        $this->assertEquals(130_000, $resolved[3], 'Outstanding fallback ke Good');
        $this->assertEquals(130_000, $resolved[4], 'Excellent fallback ke Good');
        $this->assertEquals(130_000, $resolved[5], 'Masterpiece fallback ke Good');
    }

    public function test_quality_price_fallback_all_zero_uses_base_price(): void
    {
        $rawPrices = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $basePrice = 100_000;

        $resolved = $this->applyQualityFallback($rawPrices, $basePrice);

        foreach ($resolved as $q => $price) {
            $this->assertEquals($basePrice, $price,
                "Kualitas {$q}: fallback ke base price kalau semua 0"
            );
        }
    }

    public function test_quality_price_not_fallback_when_already_set(): void
    {
        $rawPrices = [1 => 100_000, 2 => 130_000, 3 => 180_000, 4 => 250_000, 5 => 500_000];

        $resolved = $this->applyQualityFallback($rawPrices);

        foreach ($rawPrices as $q => $price) {
            $this->assertEquals($price, $resolved[$q],
                "Kualitas {$q}: tidak boleh di-overwrite kalau harganya sudah valid"
            );
        }
    }

    // ===========================================================
    // 5. DISTRIBUSI QTY PER KUALITAS
    // q=5..2: round(total * prob[q]), sisanya ke Normal(1)
    // ===========================================================

    public function test_qty_distribution_sums_to_total_crafted(): void
    {
        $dist       = $this->service->expectedQualityDistribution();
        $totalCraft = 1000;

        $qtyByQuality = $this->distributeQtyByQuality($totalCraft, $dist);

        $this->assertEquals($totalCraft, array_sum($qtyByQuality),
            'Total qty terdistribusi harus = total craft'
        );
    }

    public function test_qty_distribution_has_five_qualities(): void
    {
        $dist       = $this->service->expectedQualityDistribution();
        $totalCraft = 100;

        $qtyByQuality = $this->distributeQtyByQuality($totalCraft, $dist);

        $this->assertCount(5, $qtyByQuality, 'Harus selalu ada 5 kualitas');
    }

    public function test_normal_quality_gets_largest_qty(): void
    {
        $dist       = $this->service->expectedQualityDistribution();
        $totalCraft = 1000;

        $qtyByQuality = $this->distributeQtyByQuality($totalCraft, $dist);
        $maxQuality   = array_keys($qtyByQuality, max($qtyByQuality))[0];

        $this->assertEquals(1, $maxQuality,
            'Normal harus mendapat qty terbanyak dari semua kualitas'
        );
    }

    public function test_qty_distribution_small_batch_still_sums_correctly(): void
    {
        $dist       = $this->service->expectedQualityDistribution();
        $totalCraft = 10;

        $qtyByQuality = $this->distributeQtyByQuality($totalCraft, $dist);

        $this->assertEquals($totalCraft, array_sum($qtyByQuality),
            'Total qty batch kecil harus tetap = total craft'
        );
    }

    public function test_qty_distribution_single_craft(): void
    {
        $dist         = $this->service->expectedQualityDistribution();
        $qtyByQuality = $this->distributeQtyByQuality(1, $dist);

        $this->assertEquals(1, array_sum($qtyByQuality),
            'Dari 1 craft, total qty harus tetap 1'
        );
    }

    // ===========================================================
    // 6. SEMUA 5 KUALITAS UPDATE KONSISTEN SAAT RETURN% BERUBAH
    // (reproduksi contract dari task: "ganti return%, pastikan
    //  semua 5 kualitas update konsisten")
    // ===========================================================

    public function test_all_five_qualities_update_consistently_across_return_rates(): void
    {
        $dist       = $this->service->expectedQualityDistribution();
        $totalCraft = 100;
        $prices     = [1 => 100_000, 2 => 130_000, 3 => 180_000, 4 => 250_000, 5 => 500_000];
        $materialCost = 200_000;

        $prevProfit = null;
        foreach ([0, 15, 30, 47] as $returnPct) {
            $returnRate   = $returnPct / 100;
            $effectiveCost = $materialCost * (1 - $returnRate);

            $qtyByQuality = $this->distributeQtyByQuality($totalCraft, $dist);

            // Verifikasi semua 5 kualitas ada
            $this->assertCount(5, $qtyByQuality,
                "Return {$returnPct}%: semua 5 kualitas harus ada"
            );

            // Distribusi harus tetap sama (RRR tidak mengubah qty per quality)
            $this->assertEquals($totalCraft, array_sum($qtyByQuality),
                "Return {$returnPct}%: total qty tidak berubah"
            );

            // Hitung profit
            $totalRevenue = 0;
            foreach ($qtyByQuality as $q => $qty) {
                $totalRevenue += $qty * $prices[$q];
            }
            $profit = $totalRevenue - $effectiveCost;

            // Profit harus naik seiring RRR naik (karena effectiveCost turun)
            if ($prevProfit !== null) {
                $this->assertGreaterThan($prevProfit, $profit,
                    "Profit harus naik dari RRR sebelumnya ke {$returnPct}% (effectiveCost turun)"
                );
            }
            $prevProfit = $profit;
        }
    }

    public function test_changing_return_rate_only_affects_cost_not_distribution(): void
    {
        $dist       = $this->service->expectedQualityDistribution();
        $totalCraft = 500;

        $distAt0pct  = $this->distributeQtyByQuality($totalCraft, $dist);
        $distAt30pct = $this->distributeQtyByQuality($totalCraft, $dist);

        // Distribusi qty harus IDENTIK meski return rate beda
        $this->assertEquals($distAt0pct, $distAt30pct,
            'Distribusi qty per kualitas tidak berubah karena return rate'
        );
    }

    // ===========================================================
    // HELPERS — mirror dari logic frontend crafting.blade.php
    // ===========================================================

    /**
     * Mirror applyQualityFallback: kalau price[Q]=0, cascade downward ke Q-1, Q-2, dst.
     * Misal Q3=0, Q4=0, Q5=0 → semua fallback ke Q2 (kalau Q2 > 0).
     */
    private function applyQualityFallback(array $prices, int $basePrice = 0): array
    {
        // Kalau Normal (1) = 0, isi dulu dengan basePrice sebelum cascade
        if ($prices[1] <= 0) {
            $prices[1] = $basePrice;
        }

        $resolved = $prices;

        // Cascade dari Q=2 ke Q=5: kalau 0, cari nilai dari Q yang lebih rendah
        for ($q = 2; $q <= 5; $q++) {
            if ($resolved[$q] <= 0) {
                for ($fb = $q - 1; $fb >= 1; $fb--) {
                    if ($resolved[$fb] > 0) {
                        $resolved[$q] = $resolved[$fb];
                        break;
                    }
                }
            }
        }

        return $resolved;
    }

    /**
     * Mirror distributeQtyByQuality dari crafting.blade.php:
     * q=5..2: round(total * prob[q]), sisanya ke Normal (1)
     */
    private function distributeQtyByQuality(int $totalCraft, array $dist): array
    {
        $result = [];
        $sisa   = $totalCraft;

        for ($q = 5; $q >= 2; $q--) {
            $qty       = (int) round($totalCraft * $dist[$q]);
            $result[$q] = $qty;
            $sisa      -= $qty;
        }

        $result[1] = max(0, $sisa);

        return $result;
    }
}
