<?php

namespace App\Services;

class CraftingQualityService
{
    /**
     * Probabilitas crafting untuk tiap kualitas
     * @return array
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
     * Daftar ID kategori (dari Albion Data API) yang memiliki atribut Quality.
     * Mengambil referensi dari list yang ada di frontend (resources/views/market.blade.php)
     * @return array
     */
    public function getQualitySupportedCategories(): array
    {
        return [1, 154, 186, 217, 248, 270, 286, 289, 371];
    }
}
