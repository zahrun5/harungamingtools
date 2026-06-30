<?php
namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'page'      => 'required|in:fishing,flip,refine,catatan,crafting',
            'body'      => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
            'mention'   => 'nullable|string|max:50',
        ]);

        Comment::create([
            'user_id'   => Auth::id(),
            'page'      => $request->page,
            'body'      => $request->body,
            'parent_id' => $request->parent_id ?? null,
            'mention'   => $request->mention ?? null,
        ]);

        return back()->with('success', 'Komentar berhasil dikirim!');
    }

    public function destroy(Comment $comment)
    {
        if (Auth::id() !== $comment->user_id && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $comment->delete();
        return back()->with('success', 'Komentar dihapus.');
    }
}
