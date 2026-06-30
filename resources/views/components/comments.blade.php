@props(['page'])

@php
    $comments = \App\Models\Comment::where('page', $page)
        ->whereNull('parent_id')
        ->with(['user', 'replies.user'])
        ->latest()
        ->get();
@endphp

<div style="margin-top:48px;">
    <h2 style="font-family:'Fraunces',serif;color:var(--gold);font-size:1.1rem;margin-bottom:20px;">💬 Diskusi</h2>

    {{-- Form Komentar --}}
    @auth
        <form method="POST" action="/comments" id="comment-form" style="margin-bottom:28px;">
            @csrf
            <input type="hidden" name="page" value="{{ $page }}">
            <input type="hidden" name="parent_id" id="parent_id" value="">
            <input type="hidden" name="mention" id="mention" value="">

            <div id="reply-info" style="display:none;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;padding:8px 12px;margin-bottom:8px;font-size:.82rem;color:var(--text-muted);justify-content:space-between;align-items:center;">
                <span id="reply-label"></span>
                <button type="button" onclick="cancelReply()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;">✕</button>
            </div>

            <textarea name="body" id="comment-body" rows="3" placeholder="Tulis komentar atau pertanyaan..."
                style="width:100%;background:var(--bg-panel);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:8px;font-size:.9rem;outline:none;resize:vertical;font-family:'Sora',sans-serif;"
                maxlength="100" required></textarea>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
                <span style="font-size:.78rem;color:var(--text-muted);">Max 100 karakter</span>
                <button type="submit" style="background:var(--gold);color:var(--bg);font-weight:700;padding:8px 20px;border:none;border-radius:8px;cursor:pointer;font-size:.9rem;">Kirim</button>
            </div>
        </form>
    @else
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:16px;text-align:center;margin-bottom:28px;font-size:.88rem;color:var(--text-muted);">
            <a href="/login" style="color:var(--gold);">Login</a> untuk ikut diskusi.
        </div>
    @endauth

    {{-- List Komentar --}}
    @if($comments->isEmpty())
        <div style="text-align:center;color:var(--text-muted);font-size:.88rem;padding:24px 0;">
            Belum ada komentar. Jadilah yang pertama! 🎯
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:12px;">
            @foreach($comments as $comment)
                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:14px 16px;">
                    {{-- Header --}}
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <img src="{{ $comment->user->display_avatar }}" style="width:28px;height:28px;border-radius:50%;background:var(--bg-panel);">
                        <span style="font-weight:600;font-size:.88rem;">{{ $comment->user->display_name }}</span>
                        <span style="color:var(--text-muted);font-size:.78rem;margin-left:auto;">{{ $comment->created_at->diffForHumans() }}</span>
                        @if(auth()->check() && (auth()->id() === $comment->user_id || auth()->user()->role === 'admin'))
                            <form method="POST" action="/comments/{{ $comment->id }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.78rem;" onclick="return confirm('Hapus komentar ini?')">🗑</button>
                            </form>
                        @endif
                    </div>
                    {{-- Body --}}
                    <p style="font-size:.9rem;line-height:1.6;word-break:break-word;">{{ $comment->body }}</p>

                    {{-- Tombol Reply --}}
                    @auth
                        <button type="button"
                            onclick="setReply({{ $comment->id }}, '{{ $comment->user->display_name }}')"
                            style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.78rem;margin-top:8px;">
                            💬 Balas
                        </button>
                    @endauth

                    {{-- Replies --}}
                    @if($comment->replies->isNotEmpty())
                        <div style="margin-top:12px;padding-left:16px;border-left:2px solid var(--border);display:flex;flex-direction:column;gap:10px;">
                            @foreach($comment->replies as $reply)
                                <div>
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                        <img src="{{ $reply->user->display_avatar }}" style="width:22px;height:22px;border-radius:50%;background:var(--bg-panel);">
                                        <span style="font-weight:600;font-size:.82rem;">{{ $reply->user->display_name }}</span>
                                        <span style="color:var(--text-muted);font-size:.75rem;margin-left:auto;">{{ $reply->created_at->diffForHumans() }}</span>
                                        @if(auth()->check() && (auth()->id() === $reply->user_id || auth()->user()->role === 'admin'))
                                            <form method="POST" action="/comments/{{ $reply->id }}" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.75rem;" onclick="return confirm('Hapus komentar ini?')">🗑</button>
                                            </form>
                                        @endif
                                    </div>
                                    <p style="font-size:.88rem;line-height:1.6;word-break:break-word;">
                                        @if($reply->mention)
                                            <span style="color:var(--gold);">{{ $reply->mention }}</span>
                                        @endif
                                        {{ $reply->body }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
function setReply(parentId, username) {
    document.getElementById('parent_id').value = parentId;
    document.getElementById('mention').value = username;
    document.getElementById('reply-label').textContent = 'Membalas ' + username;
    document.getElementById('reply-info').style.display = 'flex';
    document.getElementById('comment-body').focus();
}

function cancelReply() {
    document.getElementById('parent_id').value = '';
    document.getElementById('mention').value = '';
    document.getElementById('reply-info').style.display = 'none';
}
</script>
