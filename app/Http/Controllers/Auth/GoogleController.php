<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
class GoogleController extends Controller
{
	public function redirect()
	{
	    return Socialite::driver('google')->stateless()->redirect();
	}
	
	public function callback()
	{
	    try {
	        $googleUser = Socialite::driver('google')->stateless()->user();
	        
	        $user = User::updateOrCreate(
	            ['google_id' => $googleUser->getId()],
	            [
	                'name'   => $googleUser->getName(),
	                'email'  => $googleUser->getEmail(),
	                'avatar' => $googleUser->getAvatar(),
                        'photo_url' => $googleUser->getAvatar(),

	            ]
	        );
	
	        Auth::login($user, true);
	        return redirect('/');
	
	    } catch (\Exception $e) {
	        \Log::error('Google login error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
	        return redirect('/login')->with('error', 'Login Google gagal.');
	    }
	}
}
