<?php

namespace App\Services;

class CraftingQualityService
{
    /**
     * Probabilitas crafting untuk tiap kualitas (tanpa focus/destiny board bonus).
     * Normal=1, Good=2, Outstanding=3, Excellent=4, Masterpiece=5
     */
    public function expectedQualityDistribution(): array
    {
        return [
            1 => 0.689, // Normal
            2 => 0.250, // Good
            3 => 0.050, // Outstanding
            4 => 0.010, // Excellent
            5 => 0.001, // Masterpiece
        ];
    }

    /**
     * Daftar ID kategori (dari DB HGT) yang memiliki atribut Quality.
     * Referensi dari list di resources/views/market.blade.php
     */
    public function getQualitySupportedCategories(): array
    {
        return [1, 154, 186, 217, 248, 270, 286, 289, 371];
    }

    /**
     * Daftar keyword nama kategori (lowercase) yang mendukung quality.
     * Digunakan saat scan materials untuk check by category name.
     */
    public function getQualityCategoryKeywords(): array
    {
        return ['weapons', 'armor', 'bags', 'capes', 'mounts', 'fishing rods', 'off-hand', 'offhand'];
    }

    /**
     * Cek apakah sebuah category_id atau nama kategori mendukung quality.
     * Prioritas: cek ID dulu, fallback ke keyword di nama.
     *
     * @param int $categoryId
     * @param string|null $categoryName
     */
    public function isCategoryQualitySupported(int $categoryId, ?string $categoryName = null): bool
    {
        if (in_array($categoryId, $this->getQualitySupportedCategories(), true)) {
            return true;
        }

        if ($categoryName) {
            $lower = strtolower($categoryName);
            foreach ($this->getQualityCategoryKeywords() as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Distribusikan kuantitas craft ke per-kualitas berdasarkan probabilitas.
     * Sisa item (akibat round) dimasukkan ke Normal (quality=1).
     *
     * Contoh: distributeByQuantity(100) =>
     *   [1 => 69, 2 => 25, 3 => 5, 4 => 1, 5 => 0]
     *   (total = 100; sisa dari round ditambah ke Normal)
     *
     * @param int $totalQty Total item yang di-craft
     * @return array<int, int> Mapping quality_id => quantity
     */
    public function distributeByQuantity(int $totalQty): array
    {
        $dist = $this->expectedQualityDistribution();
        $result = [];
        $assigned = 0;

        foreach ($dist as $quality => $prob) {
            if ($quality === 1) continue; // Normal diisi terakhir (sisa)
            $qty = (int) round($totalQty * $prob);
            $result[$quality] = $qty;
            $assigned += $qty;
        }

        // Normal = sisa setelah semua kualitas lain
        $result[1] = max(0, $totalQty - $assigned);

        // Urutkan 1-5
        ksort($result);

        return $result;
    }

    /**
     * Range harga jual relatif per kualitas.
     * Berdasarkan rata-rata premium pasar Albion (aproksimasi).
     *
     * Normal=1.00 (base), Good ~1.05-1.10, Outstanding ~1.15-1.25,
     * Excellent ~1.30-1.50, Masterpiece ~1.80-2.50
     *
     * Format: [quality_id => ['min' => float, 'max' => float, 'label' => string]]
     */
    public function getQualityReturnRange(): array
    {
        return [
            1 => ['min' => 1.00, 'max' => 1.00, 'label' => 'Normal'],
            2 => ['min' => 1.05, 'max' => 1.10, 'label' => 'Good'],
            3 => ['min' => 1.15, 'max' => 1.25, 'label' => 'Outstanding'],
            4 => ['min' => 1.30, 'max' => 1.50, 'label' => 'Excellent'],
            5 => ['min' => 1.80, 'max' => 2.50, 'label' => 'Masterpiece'],
        ];
    }

    /**
     * Hitung estimasi nilai total hasil craft berdasarkan distribusi kualitas.
     * Gunakan harga Normal sebagai base price; kualitas lain di-scale oleh range.
     *
     * @param int $totalQty
     * @param float $basePrice Harga jual Normal
     * @param bool $useMidRange Gunakan tengah range (true) atau minimum (false)
     * @return float Estimasi total nilai jual
     */
    public function estimateTotalValue(int $totalQty, float $basePrice, bool $useMidRange = true): float
    {
        $distribution = $this->distributeByQuantity($totalQty);
        $ranges = $this->getQualityReturnRange();
        $total = 0.0;

        foreach ($distribution as $quality => $qty) {
            if ($qty <= 0) continue;
            $range = $ranges[$quality];
            $multiplier = $useMidRange
                ? ($range['min'] + $range['max']) / 2
                : $range['min'];
            $total += $qty * $basePrice * $multiplier;
        }

        return round($total, 2);
    }
}
