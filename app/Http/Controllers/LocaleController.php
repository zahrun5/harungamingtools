<?php

namespace App\Http\Controllers;

class LocaleController extends Controller
{
    /**
     * Daftar locale yang didukung HGT.
     * Tambah di sini kalau nanti mau nambah bahasa lagi.
     */
    protected array $available = ['id', 'en', 'pt_BR', 'ru', 'de', 'pl'];

    public function switch(string $locale)
    {
        if (in_array($locale, $this->available, true)) {
            session(['locale' => $locale]);
        }

        return back();
    }
}
