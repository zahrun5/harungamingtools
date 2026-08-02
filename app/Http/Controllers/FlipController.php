<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Endpoint API buat Flip Mode Advance.
 *
 * Tax & handling — samain sama rumus di flip_blade.php (Mode Simple):
 *   net_sell = raw_price * (1 - tax) * (sell_order ? (1 - handling) : 1)
 *   tax      = premium ? 0.04 : 0.08
 *   handling = 0.025 (cuma kepakai kalau mode sell_order aktif)
 *
 * Buy price SELALU pakai sell_price_min (instant buy) — gak kena tax/handling,
 * karena tax/handling di Albion cuma dikenakan pas JUAL, bukan beli.
 */
class FlipController extends Controller
{
    private const TAX_NORMAL   = 0.08;
    private const TAX_PREMIUM  = 0.04;
    private const HANDLING     = 0.025;
    private const ROYAL_CITIES = ['Caerleon', 'Martlock', 'Bridgewatch', 'Lymhurst', 'Fort Sterling', 'Thetford'];

    // ============================================================
    // MODE 1: FLIP KOTA-KE-KOTA
    // GET /api/flip/opportunities
    //   ?cities=Caerleon,Martlock,Lymhurst  (wajib, min. 2)
    //   &quality=1
    //   &premium=0|1
    //   &sell_order=0|1
    //   &filter=all|has_data|profitable   (default: profitable)
    // ============================================================
    public function opportunities(Request $request)
    {
        $cities = array_filter(explode(',', $request->query('cities', '')));
        if (count($cities) < 2) {
            return response()->json(['error' => 'Pilih minimal 2 kota.'], 422);
        }

        $quality   = (int) $request->query('quality', 1);
        $premium   = $request->boolean('premium');
        $sellOrder = $request->boolean('sell_order');
        $filter    = $request->query('filter', 'profitable');

        $rows = DB::table('flip_city_prices')
            ->whereIn('city', $cities)
            ->where('quality', $quality)
            ->get()
            ->groupBy('item_id');

        $itemNames = Item::whereIn('id', $rows->keys())->pluck('name', 'id');
        $results   = [];

        foreach ($rows as $itemId => $cityRows) {
            $best = $this->bestPairing($cityRows, $premium, $sellOrder);
            if (! $best) {
                continue; // gak ada data harga sama sekali buat item ini
            }

            if ($filter === 'profitable' && $best['profit'] <= 0) {
                continue;
            }

            $results[] = array_merge($best, [
                'item_id'   => $itemId,
                'item_name' => $itemNames[$itemId] ?? '?',
            ]);
        }

        // 'all' dan 'has_data' sama-sama include item tanpa data kalau filter='all'
        // (khusus 'all', kita gak filter sama sekali di atas — biarkan lolos semua)
        usort($results, fn ($a, $b) => $b['profit'] <=> $a['profit']);

        return response()->json($results);
    }

    // ============================================================
    // Cari pasangan kota beli-jual TERBAIK dari satu item (dipakai list).
    // Popup nanti pakai versi "semua pasangan", bukan cuma yang terbaik.
    // ============================================================
    private function bestPairing($cityRows, bool $premium, bool $sellOrder): ?array
    {
        $pairings = $this->allPairings($cityRows, $premium, $sellOrder);
        return $pairings[0] ?? null; // udah diurut profit tertinggi di allPairings()
    }

    // ============================================================
    // Hitung SEMUA kombinasi kota asal->tujuan yang valid (beda kota,
    // dua-duanya punya data harga), urut dari profit tertinggi.
    // ============================================================
    private function allPairings($cityRows, bool $premium, bool $sellOrder): array
    {
        $tax = $premium ? self::TAX_PREMIUM : self::TAX_NORMAL;
        $pairings = [];

        foreach ($cityRows as $origin) {
            if (! $origin->sell_price_min) {
                continue; // gak bisa instant-buy di kota ini, skip sebagai asal
            }

            foreach ($cityRows as $dest) {
                if ($dest->city === $origin->city) {
                    continue;
                }

                $rawSell = $sellOrder ? $dest->sell_price_min : $dest->buy_price_max;
                if (! $rawSell) {
                    continue; // gak ada harga jual valid di kota tujuan
                }

                $netSell = $rawSell * (1 - $tax) * ($sellOrder ? (1 - self::HANDLING) : 1);
                $profit  = $netSell - $origin->sell_price_min;

                $pairings[] = [
                    'origin_city'      => $origin->city,
                    'origin_price'     => (int) $origin->sell_price_min,
                    'dest_city'        => $dest->city,
                    'dest_price_raw'   => (int) $rawSell,
                    'dest_price_net'   => round($netSell),
                    'profit'           => round($profit),
                    'margin_pct'       => $origin->sell_price_min > 0
                        ? round(($profit / $origin->sell_price_min) * 100, 1)
                        : 0,
                    'origin_fetched_at' => $origin->fetched_at,
                    'dest_fetched_at'   => $dest->fetched_at,
                ];
            }
        }

        usort($pairings, fn ($a, $b) => $b['profit'] <=> $a['profit']);
        return $pairings;
    }

    // ============================================================
    // MODE 1 - DETAIL POPUP: semua pasangan kota buat SATU item
    // GET /api/flip/item/{item}/pairings?cities=...&quality=&premium=&sell_order=
    // ============================================================
    public function itemPairings(Request $request, Item $item)
    {
        $cities = array_filter(explode(',', $request->query('cities', '')));
        $quality   = (int) $request->query('quality', 1);
        $premium   = $request->boolean('premium');
        $sellOrder = $request->boolean('sell_order');

        $cityRows = DB::table('flip_city_prices')
            ->where('item_id', $item->id)
            ->where('quality', $quality)
            ->when(count($cities) > 0, fn ($q) => $q->whereIn('city', $cities))
            ->get();

        return response()->json([
            'item_id'   => $item->id,
            'item_name' => $item->name,
            'pairings'  => $this->allPairings($cityRows, $premium, $sellOrder),
        ]);
    }

    // ============================================================
    // MODE 2: FLIP KE BLACK MARKET
    // GET /api/flip/blackmarket?quality=1&filter=profitable
    // Tanpa city param (selalu semua Royal city + Caerleon jadi sumber beli),
    // tanpa premium/sell_order (Black Market tax-free & sell-only).
    // ============================================================
    public function blackmarket(Request $request)
    {
        $quality = (int) $request->query('quality', 1);
        $filter  = $request->query('filter', 'profitable');

        $rows = DB::table('flip_city_prices')
            ->whereIn('city', array_merge(self::ROYAL_CITIES, ['Black Market']))
            ->where('quality', $quality)
            ->get()
            ->groupBy('item_id');

        $itemNames = Item::whereIn('id', $rows->keys())->pluck('name', 'id');
        $results   = [];

        foreach ($rows as $itemId => $cityRows) {
            $bm = $cityRows->firstWhere('city', 'Black Market');
            if (! $bm || ! $bm->buy_price_max) {
                continue; // Black Market gak punya buy order aktif buat item ini
            }

            // Cari kota sumber TERMURAH (instant-buy) di antara Royal cities
            $cheapest = $cityRows
                ->filter(fn ($r) => $r->city !== 'Black Market' && $r->sell_price_min)
                ->sortBy('sell_price_min')
                ->first();

            if (! $cheapest) {
                continue; // gak ada sumber beli sama sekali
            }

            $profit = $bm->buy_price_max - $cheapest->sell_price_min; // BM tax-free, gak ada potongan

            if ($filter === 'profitable' && $profit <= 0) {
                continue;
            }

            $results[] = [
                'item_id'          => $itemId,
                'item_name'        => $itemNames[$itemId] ?? '?',
                'origin_city'      => $cheapest->city,
                'origin_price'     => (int) $cheapest->sell_price_min,
                'origin_is_caerleon' => $cheapest->city === 'Caerleon', // false = perlu extra transport ke Caerleon
                'bm_price'         => (int) $bm->buy_price_max,
                'profit'           => $profit,
                'margin_pct'       => round(($profit / $cheapest->sell_price_min) * 100, 1),
                'bm_fetched_at'    => $bm->fetched_at,
            ];
        }

        usort($results, fn ($a, $b) => $b['profit'] <=> $a['profit']);

        return response()->json($results);
    }
}
