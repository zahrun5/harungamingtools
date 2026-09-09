<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\StatusLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Query statuses dengan filter visibility
        $statuses = Status::with(['user', 'likes'])
            ->where(function ($query) use ($user) {
                // Tampilkan status public
                $query->where('visibility', 'public');
                
                // Jika user login dan punya server, tampilkan juga status dari server yang sama
                if ($user && $user->albion_server) {
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('visibility', 'server')
                          ->where('server', $user->albion_server);
                    });
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('social.index', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:500',
            'visibility' => 'required|in:public,server',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
        ]);

        $user = Auth::user();
        
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('statuses', 'public');
        }
        
        $status = Status::create([
            'user_id' => $user->id,
            'body' => $validated['body'],
            'image' => $imagePath,
            'visibility' => $validated['visibility'],
            'server' => $validated['visibility'] === 'server' ? $user->albion_server : null,
        ]);

        return redirect()->route('social.index')->with('success', 'Status berhasil diposting!');
    }

    public function destroy(Status $status)
    {
        $user = Auth::user();
        
        // Hanya owner atau admin yang bisa hapus
        if ($status->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Hapus image jika ada
        if ($status->image) {
            \Storage::disk('public')->delete($status->image);
        }

        $status->delete();

        return redirect()->route('social.index')->with('success', 'Status berhasil dihapus!');
    }

    public function like(Status $status)
    {
        $user = Auth::user();
        
        $existingLike = StatusLike::where('status_id', $status->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            $status->decrement('likes_count');
            $liked = false;
        } else {
            // Like
            StatusLike::create([
                'status_id' => $status->id,
                'user_id' => $user->id,
            ]);
            $status->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $status->fresh()->likes_count,
        ]);
    }
}
