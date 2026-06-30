<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'custom_name' => 'nullable|string|max:20|unique:users,custom_name,'.Auth::id().',id',
            'avatar_seed' => 'nullable|string|max:100',
            'avatar_style' => 'nullable|string|max:50',
        ]);

        Auth::user()->update([
            'custom_name'  => $request->custom_name ?: null,
            'avatar_seed'  => $request->avatar_seed ?: null,
            'avatar_style' => $request->avatar_style ?? 'pixel-art',
        ]);

        return redirect('/profile')->with('success', 'Profil berhasil diperbarui!');
    }
}
