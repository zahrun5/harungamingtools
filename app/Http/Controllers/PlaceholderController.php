<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlaceholderController extends Controller
{
    public function social()
    {
        return view('placeholder', [
            'title' => 'Social',
        ]);
    }

    public function socialPost(int $id)
    {
        return view('placeholder', [
            'title' => 'Detail Postingan',
        ]);
    }

    public function notifications()
    {
        return view('placeholder', [
            'title' => 'Notifikasi',
        ]);
    }
}