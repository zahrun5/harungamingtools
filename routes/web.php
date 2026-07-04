<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramAuthController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DeathRecapController;

// ─── Auth Google ─────────────────────────────────────────────────────────────
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// ─── Auth Telegram ────────────────────────────────────────────────────────────
Route::get('/login', [TelegramAuthController::class, 'login'])->name('login');
Route::get('/auth/telegram/callback', [TelegramAuthController::class, 'callback']);

// ─── Public Pages ─────────────────────────────────────────────────────────────
Route::get('/', fn() => view('home'));
Route::get('/catatan', fn() => view('catatan'));
Route::get('/crafting', fn() => view('crafting'));

Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
});

// ─── Kalkulator ───────────────────────────────────────────────────────────────
Route::get('/kalkulator/fishing', fn() => view('kalkulator.fishing'));
Route::get('/kalkulator/flip',    fn() => view('kalkulator.flip'));
Route::get('/kalkulator/refine',  fn() => view('kalkulator.refine'));

// ─── API Publik ───────────────────────────────────────────────────────────────
Route::post('/api/catat-aktivitas', function (\Illuminate\Http\Request $request) {
    if (auth()->check()) {
        \App\Models\CalculatorUsage::create([
            'user_id'         => auth()->id(),
            'calculator_type' => $request->input('type', 'fishing'),
        ]);
    }
    return response()->json(['ok' => true]);
});

Route::post('/api/bot/catat-aktivitas', function (\Illuminate\Http\Request $request) {
    if ($request->input('secret') !== config('services.bot.secret')) {
        return response()->json(['error' => 'unauthorized'], 403);
    }
    $user = \App\Models\User::where('telegram_id', $request->input('telegram_id'))->first();
    if (!$user) {
        return response()->json(['error' => 'user not found'], 404);
    }
    \App\Models\CalculatorUsage::create([
        'user_id'         => $user->id,
        'calculator_type' => $request->input('type', 'refine'),
    ]);
    return response()->json(['ok' => true]);
});

// ─── Market ───────────────────────────────────────────────────────────────────
Route::get('/market',                  [MarketController::class, 'index']);
Route::get('/api/market/categories',   [MarketController::class, 'categories']);
Route::get('/api/market/items',        [MarketController::class, 'items']);
Route::get('/api/market/item/{id}',    [MarketController::class, 'itemDetail']);
Route::post('/api/market/item/{id}/refresh-prices', [MarketController::class, 'refreshPrices']);
Route::post('/api/market/category/{categoryId}/refresh-prices', [MarketController::class, 'refreshCategoryPrices']);
Route::post('/api/market/item/{id}/refresh-single', [MarketController::class, 'refreshItemPriceSingle']);

// === Death Recap ===
Route::get('/death-recap', [DeathRecapController::class, 'index']);
Route::post('/api/death-recap/search', [DeathRecapController::class, 'search']);
Route::post('/api/death-recap/load-more', [DeathRecapController::class, 'loadMore']);
Route::get('/death-recap/event/{eventId}', [DeathRecapController::class, 'show']);

// ─── Auth Required ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'daily.bonus'])->group(function () {


    // Dashboard & Profile
    Route::get('/dashboard', fn() => view('dashboard'));
    Route::get('/profile',       [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Comments
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Dev Tools (admin only)
    Route::get('/dev/test', function () {
        if (auth()->user()->role !== 'admin') abort(403);
        return view('dev.test');
    })->name('dev.test');

    Route::get('/dev/recipe-items', [MarketController::class, 'recipeItems'])->name('dev.recipe-items');
    Route::post('/dev/save-items',  [MarketController::class, 'saveItems'])->name('dev.save-items');
    Route::get('/dev/saved-items',  [MarketController::class, 'savedItems'])->name('dev.saved-items');
    Route::patch('/dev/items/{id}/category', [MarketController::class, 'updateItemCategory'])->name('dev.update-category');

});