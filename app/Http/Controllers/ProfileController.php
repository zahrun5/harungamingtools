<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\AlbionApiService;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'username' => 'nullable|string|max:20|alpha_dash|unique:users,username,'.Auth::id().',id',
            'avatar_seed' => 'nullable|string|max:100',
            'avatar_style' => 'nullable|string|max:50',
            'is_public' => 'nullable|boolean',
        ]);
        Auth::user()->update([
            'username'     => $request->username ?: null,
            'avatar_seed'  => $request->avatar_seed ?: null,
            'avatar_style' => $request->avatar_style ?? 'pixel-art',
            'is_public'    => $request->boolean('is_public'),
        ]);

        return redirect('/profile')->with('success', 'Profil berhasil diperbarui!');
    }
    
    public function showPublic(User $user)
    {
        $isOwnProfile = Auth::check() && Auth::id() === $user->id;

        // Profil private cuma boleh diliat pemiliknya sendiri (atau admin, buat moderasi)
        abort_unless($user->is_public || $isOwnProfile || Auth::user()?->role === 'admin', 404);

        return view('profile.public', [
            'user' => $user,
            'isOwnProfile' => $isOwnProfile,
        ]);
    }

    /**
     * Tahap 2 — User submit IGN + server buat mulai proses verifikasi Albion.
     * Nyari player via API, generate kode sekali pakai, status jadi 'pending'.
     * Admin nanti liat kode ini di /dev/verifications dan kirim via in-game mail.
     */
    public function submitAlbionVerification(Request $request, AlbionApiService $albionApi)
    {
        $request->validate([
            'albion_server' => 'required|in:americas,europe,asia',
            'albion_ign'    => 'required|string|max:50',
        ]);

        $user = Auth::user();

        // Rate limit: cegah spam pengajuan baru selama masih ada yang pending & belum expired
        if ($user->verification_status === 'pending'
            && $user->verification_code_expires_at
            && $user->verification_code_expires_at->isFuture()
        ) {
            return back()->with('error', 'Masih ada pengajuan verifikasi aktif. Tunggu admin kirim kode dulu, atau tunggu sampai expired.');
        }

        $player = $albionApi->searchPlayer($request->albion_ign, $request->albion_server);

        if (! $player) {
            return back()->with('error', "IGN \"{$request->albion_ign}\" tidak ditemukan di server ".ucfirst($request->albion_server).'. Cek lagi ejaannya.');
        }

        $user->update([
            'albion_ign'                   => $player['Name'], // simpan nama persis dari API, bukan input mentah user
            'albion_player_id'             => $player['Id'],
            'albion_server'                => $request->albion_server,
            'verification_status'          => 'pending',
            'verification_code'            => $this->generateVerificationCode(),
            'verification_code_expires_at' => now()->addHours(48),
        ]);

        return redirect('/profile')->with('success', 'Pengajuan verifikasi terkirim! Tunggu admin kirim kode via mail in-game, lalu masukkan kodenya di halaman ini.');
    }

    /**
     * Tahap 2 — User submit kode yang diterima lewat mail in-game.
     * Kalau cocok, langsung fetch & cache data lengkap dari Albion API.
     */
    public function confirmAlbionVerification(Request $request, AlbionApiService $albionApi)
    {
        $request->validate([
            'verification_code' => 'required|string|max:10',
        ]);

        $user = Auth::user();

        if ($user->verification_status !== 'pending' || ! $user->verification_code) {
            return back()->with('error', 'Tidak ada pengajuan verifikasi yang menunggu kode.');
        }

        if ($user->verification_code_expires_at && $user->verification_code_expires_at->isPast()) {
            return back()->with('error', 'Kode verifikasi sudah kedaluwarsa. Silakan ajukan ulang.');
        }

        // Case-insensitive: user ngetik manual dari mail, gampang salah kapital
        if (strcasecmp(trim($request->verification_code), $user->verification_code) !== 0) {
            return back()->with('error', 'Kode verifikasi salah. Cek lagi mail in-game kamu.');
        }

        $detail = $albionApi->getPlayerDetail($user->albion_player_id, $user->albion_server);

        if (! $detail) {
            return back()->with('error', 'Kode benar, tapi gagal ambil data dari Albion API. Coba lagi sebentar.');
        }

        $user->update(array_merge(
            $albionApi->mapToUserAttributes($detail),
            [
                'verification_status'          => 'verified',
                'verified_at'                  => now(),
                'verification_code'            => null,
                'verification_code_expires_at' => null,
            ]
        ));

        return redirect('/profile')->with('success', 'Verifikasi berhasil! Identitas Albion kamu sekarang tampil di profil.');
    }

    /**
     * Tahap 2 — Halaman admin: list semua user dengan verification_status = pending,
     * buat dicontek IGN + kodenya lalu dikirim manual via mail in-game.
     */
    public function adminVerifications()
    {
        $pendingUsers = User::where('verification_status', 'pending')
            ->orderBy('verification_code_expires_at')
            ->get();

        return view('dev.verifications', [
            'pendingUsers' => $pendingUsers,
        ]);
    }

    /**
     * Tahap 2 — Admin menolak pengajuan verifikasi (mis. klaim IGN diragukan).
     * Reset field verifikasi biar user bisa ajukan ulang dari awal.
     */
    public function rejectAlbionVerification(User $user)
    {
        $user->update([
            'verification_status'          => 'rejected',
            'verification_code'            => null,
            'verification_code_expires_at' => null,
        ]);

        return back()->with('success', "Pengajuan {$user->albion_ign} ditolak.");
    }

    /**
     * Generate kode verifikasi format HGT-XXXXXX, charset dibatasi biar nggak
     * ada karakter ambigu (0/O, 1/I/L) — gampang salah ketik pas disalin manual
     * dari mail in-game.
     */
    private function generateVerificationCode(): string
    {
        $charset = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $code = collect(str_split($charset))->random(6)->implode('');

        return "HGT-{$code}";
    }
}
