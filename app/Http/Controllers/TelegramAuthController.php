<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramAuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function callback(Request $request)
    {
        $data = $request->all();

        if (!$this->verifyTelegramData($data)) {
            abort(403, 'Verifikasi Telegram gagal.');
        }

        $user = User::updateOrCreate(
            ['telegram_id' => $data['id']],
            [
                'telegram_username' => $data['username'] ?? null,
                'name' => $data['first_name'] . (isset($data['last_name']) ? ' ' . $data['last_name'] : ''),
                'photo_url' => $data['photo_url'] ?? null,
                'last_login_at' => now(),
            ]
        );

        Auth::login($user);

        return redirect('/dashboard');
    }

    private function verifyTelegramData(array $data): bool
    {
        if (!isset($data['hash'])) {
            return false;
        }

        $checkHash = $data['hash'];
        unset($data['hash']);

        $dataCheckArr = [];
        foreach ($data as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        sort($dataCheckArr);
        $dataCheckString = implode("\n", $dataCheckArr);

        $secretKey = hash('sha256', config('services.telegram.bot_token'), true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($hash, $checkHash);
    }
}
