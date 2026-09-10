<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramAuthController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DeathRecapController;
use App\Http\Controllers\CraftingController;
use App\Http\Controllers\CraftingStationAdminController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ReelDeveloperController;
use App\Http\Controllers\ReelController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\FlipController;
use App\Http\Controllers\SocialController;

// ─── Server & Locale ────────────────────────────────────────────────────
Route::get('/server/{server}', function (string $server) {
    abort_unless(in_array($server, ['americas', 'europe', 'asia']), 404);
    session(['server' => $server]);
    return back();
})->name('server.switch');

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');

// ─── Sitemap ────────────────────────────────────────────────────────────
Route::get('/sitemap.xml', function () {
    return response()->view('sitemap')->header('Content-Type', 'text/xml');
});

// ─── Auth Google ────────────────────────────────────────────────────────
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// ─── Auth Telegram ──────────────────────────────────────────────────────
Route::get('/login', [TelegramAuthController::class, 'login'])->name('login');
Route::get('/auth/telegram/callback', [TelegramAuthController::class, 'callback']);

Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
});

// ─── Public Pages ───────────────────────────────────────────────────────
Route::get('/', fn() => view('home'));
Route::get('/catatan', fn() => view('catatan'));
Route::get('/crafting', fn() => view('crafting'));

// ─── Kalkulator ──────────────────────────────────────────────────────────
Route::get('/kalkulator/fishing', fn() => view('kalkulator.fishing'));
Route::get('/kalkulator/flip',    fn() => view('kalkulator.flip'));
Route::get('/kalkulator/refine',  fn() => view('kalkulator.refine'));

// ─── flip ──────────────────────────────────────────────────────────
Route::get('/flip/advance', [FlipController::class, 'advance'])->name('flip.advance');
Route::post('/flip/scan', [FlipController::class, 'scan'])->name('flip.scan');
Route::get('/flip/scan/results', [FlipController::class, 'results'])->name('flip.scan.results');
Route::get('/flip/top-opportunities', [FlipController::class, 'topOpportunities'])->name('flip.top');
// ─── Crafting ────────────────────────────────────────────────────────────
Route::get('/crafting/{station}', [CraftingController::class, 'index'])->name('crafting.show');
Route::get('/mages-tower', [CraftingController::class, 'index'])->name('mages-tower');

Route::prefix('api/crafting')->group(function () {
    // Legacy — dipakai Mage's Tower, TIDAK DIUBAH sama sekali.
    // Default station di controller = 'mage-tower', jadi URL ini tetap
    // jalan persis seperti sebelumnya tanpa perlu ubah blade Mage's Tower.
    Route::get('/categories', [CraftingController::class, 'categories']);
    Route::get('/items', [CraftingController::class, 'items']);
    Route::get('/item/{id}', [CraftingController::class, 'itemDetail']);
    Route::post('/item/{id}/refresh-prices', [CraftingController::class, 'refreshPrices']);
    Route::post('/item/{id}/refresh-price', [CraftingController::class, 'refreshItemPriceSingle']);
    Route::post('/category/{categoryId}/refresh-prices', [CraftingController::class, 'refreshCategoryPrices']);

    // Advance Mode - materials endpoint
    Route::get('/advance/materials', [CraftingController::class, 'advanceMaterials']);

    // Station lain (Hunter's Lodge, dst) — butuh slug di URL.
    // Harus diletakkan SETELAH rute /categories dan /items di atas,
    // supaya tidak "menangkap" duluan request ke /api/crafting/categories.
    Route::get('/{station}/categories', [CraftingController::class, 'categories']);
    Route::get('/{station}/items', [CraftingController::class, 'items']);
    Route::get('/{station}/advance/materials', [CraftingController::class, 'advanceMaterials']);
});

// ─── Market ─────────────────────────────────────────────────────────────
Route::get('/market', [MarketController::class, 'index']);
Route::get('/api/market/categories', [MarketController::class, 'categories']);
Route::get('/api/market/items', [MarketController::class, 'items']);
Route::get('/api/market/item/{id}', [MarketController::class, 'itemDetail']);
Route::post('/api/market/item/{id}/refresh-prices', [MarketController::class, 'refreshPrices']);
Route::post('/api/market/category/{categoryId}/refresh-prices', [MarketController::class, 'refreshCategoryPrices']);
Route::post('/api/market/item/{id}/refresh-single', [MarketController::class, 'refreshItemPriceSingle']);
Route::get('/api/market/item/{id}/price-history', [MarketController::class, 'priceHistory']);

// ─── Death Recap ────────────────────────────────────────────────────────
Route::get('/death-recap', [DeathRecapController::class, 'index']);
Route::post('/api/death-recap/search', [DeathRecapController::class, 'search']);
Route::post('/api/death-recap/load-more', [DeathRecapController::class, 'loadMore']);
Route::get('/death-recap/event/{eventId}', [DeathRecapController::class, 'show']);

// ─── Reels ──────────────────────────────────────────────────────────────
Route::get('/reels', [ReelController::class, 'index'])->name('reels.index');
Route::post('/reels/more', [ReelController::class, 'more'])->name('reels.more');
Route::post('/api/reels/track-impression', [\App\Http\Controllers\Api\ReelImpressionController::class, 'track'])->name('reels.track-impression');

// ─── API Publik (bot & tracking) ───────────────────────────────────────
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

// ─── Dev Tools — Crafting Station Admin (di luar grup 'admin', cek ulang jika perlu) ───
Route::get('/dev/crafting-stations', [CraftingStationAdminController::class, 'index']);
Route::get('/dev/crafting-stations/categories-tree', [CraftingStationAdminController::class, 'categoriesTree']);
Route::get('/dev/crafting-stations/categories-search', [CraftingStationAdminController::class, 'searchCategories']);
Route::get('/dev/crafting-stations/{id}/categories', [CraftingStationAdminController::class, 'stationCategories']);
Route::post('/dev/crafting-stations', [CraftingStationAdminController::class, 'store']);
Route::post('/dev/crafting-stations/{id}/categories', [CraftingStationAdminController::class, 'syncCategories']);
Route::delete('/dev/crafting-stations/{id}', [CraftingStationAdminController::class, 'destroy']);

// ─── Auth Required ──────────────────────────────────────────────────────
Route::middleware(['auth', 'daily.bonus'])->group(function () {

    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard'));

	// Profile
	Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
	Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
	Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
	Route::post('/profile/albion/submit', [ProfileController::class, 'submitAlbionVerification'])->name('profile.albion.submit');
	Route::post('/profile/albion/confirm', [ProfileController::class, 'confirmAlbionVerification'])->name('profile.albion.confirm');
	Route::get('/profile/{user}', [ProfileController::class, 'showPublic'])->name('profile.public');
	
	// Redirect alamat lama biar link yang udah kesebar gak mati
	Route::get('/u/{user}', function ($user) {
	    return redirect()->route('profile.public', ['user' => $user], 301);
	});
    // Build
    // Catatan: middleware('auth') di sini sebelumnya di-nest lagi padahal
    // grup luar sudah 'auth' — redundan, jadi dihapus tanpa mengubah behavior.
    Route::get('/builds', [BuildController::class, 'index'])->name('builds.index');
    Route::get('/builds/items/search', [BuildController::class, 'searchItems'])->name('builds.items.search');
    Route::get('/builds/create', [BuildController::class, 'create'])->name('builds.create');
    Route::post('/builds', [BuildController::class, 'store'])->name('builds.store');
    Route::get('/builds/{build}/edit', [BuildController::class, 'edit'])->name('builds.edit');
    Route::put('/builds/{build}', [BuildController::class, 'update'])->name('builds.update');
    Route::delete('/builds/{build}', [BuildController::class, 'destroy'])->name('builds.destroy');
    Route::post('/builds/{build}/activate', [BuildController::class, 'activate'])->name('builds.activate');
    Route::patch('/builds/{build}/slot', [BuildController::class, 'updateSlot'])->name('builds.slot.update');

	//top tier and leaderboard
	Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
			
    // Comments
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Social
    Route::get('/social', [SocialController::class, 'index'])->name('social.index');
    Route::post('/social', [SocialController::class, 'store'])->name('social.store');
    Route::delete('/social/{status}', [SocialController::class, 'destroy'])->name('social.destroy');
    Route::post('/social/{status}/like', [SocialController::class, 'like'])->name('social.like');

    // Notifikasi (placeholder — struktur data belum final, dibangun detail di sesi terpisah)
    Route::get('/notifikasi', [PlaceholderController::class, 'notifications'])->name('notifications.index');

    // Dev Tools (admin only) — semua route di bawah ini otomatis dikunci middleware 'admin'
    Route::middleware('admin')->prefix('dev')->name('dev.')->group(function () {
        Route::get('/', fn () => view('dev.index'))->name('index');
        Route::get('/test', fn() => view('dev.test'))->name('test');
	
	Route::prefix('reels')->name('reels.')->group(function () {
	    Route::get('/', [ReelDeveloperController::class, 'index'])->name('index');
	    Route::post('/', [ReelDeveloperController::class, 'store'])->name('store');
	    Route::post('/import', [ReelDeveloperController::class, 'importFromChannel'])->name('import');
	    Route::patch('/{reel}/toggle', [ReelDeveloperController::class, 'toggleActive'])->name('toggle');
	    Route::patch('/{reel}/sponsored', [ReelDeveloperController::class, 'updateSponsored'])->name('sponsored');
	    Route::delete('/{reel}', [ReelDeveloperController::class, 'destroy'])->name('destroy');
	
	    Route::patch('/bulk-action', [ReelDeveloperController::class, 'bulkAction'])->name('bulk-action');
	
	    Route::patch('/{reel}/approve', [ReelDeveloperController::class, 'approveReel'])->name('approve');
	    Route::patch('/channels/{channel}/approve', [ReelDeveloperController::class, 'approveChannel'])->name('channels.approve');
	    Route::patch('/channels/{channel}/reject', [ReelDeveloperController::class, 'rejectChannel'])->name('channels.reject');
	});

        Route::get('/recipe-items', [MarketController::class, 'recipeItems'])->name('recipe-items');
        Route::post('/save-items', [MarketController::class, 'saveItems'])->name('save-items');
        Route::get('/saved-items', [MarketController::class, 'savedItems'])->name('saved-items');
        Route::patch('/items/{id}/category', [MarketController::class, 'updateItemCategory'])->name('update-category');

        Route::get('/verifications', [ProfileController::class, 'adminVerifications'])->name('verifications');
        Route::post('/verifications/{user}/reject', [ProfileController::class, 'rejectAlbionVerification'])->name('verifications.reject');
    });
});