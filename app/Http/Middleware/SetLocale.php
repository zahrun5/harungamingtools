<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Locale default kalau session belum pernah diisi (user baru pertama kali buka).
     */
    protected string $default = 'id';

    protected array $available = ['id', 'en', 'pt_BR', 'ru', 'de', 'pl'];

    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', $this->default);

        if (!in_array($locale, $this->available, true)) {
            $locale = $this->default;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
