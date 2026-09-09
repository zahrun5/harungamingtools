@extends('layouts.app')

@section('title', 'Social - HarunGamingTools')

@section('content')
<div class="wrap" style="max-width:680px;">
    @if(session('success'))
    <div style="background:var(--bg-card);border:1px solid var(--teal);border-radius:8px;padding:12px 16px;margin-bottom:20px;color:var(--teal);font-size:0.9rem;">
        {{ session('success') }}
    </div>
    @endif

    @auth
    <!-- Form Post Status -->
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:24px;">
        <form action="{{ route('social.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display:flex;gap:12px;margin-bottom:16px;">
                <img src="{{ auth()->user()->display_avatar }}" style="width:42px;height:42px;border-radius:50%;flex-shrink:0;">
                <div style="flex:1;">
                    <textarea 
                        name="body" 
                        placeholder="Apa yang kamu pikirkan?" 
                        required
                        maxlength="500"
                        style="width:100%;min-height:80px;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;padding:12px;color:var(--text);font-family:'Sora',sans-serif;font-size:0.9rem;resize:vertical;"
                    >{{ old('body') }}</textarea>
                    @error('body')
                    <div style="color:#ff6b6b;font-size:0.8rem;margin-top:6px;">{{ $message }}</div>
                    @enderror
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" style="display:none;margin-top:12px;position:relative;">
                        <img id="previewImg" style="max-width:100%;border-radius:8px;border:1px solid var(--border);">
                        <button type="button" id="removeImage" style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.7);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;">×</button>
                    </div>
                </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div style="display:flex;gap:12px;align-items:center;">
                    <div style="display:flex;gap:8px;">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="radio" name="visibility" value="public" checked style="accent-color:var(--gold);">
                            <span style="font-size:0.85rem;color:var(--text);">Public</span>
                        </label>
                        @if(auth()->user()->albion_server)
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="radio" name="visibility" value="server" style="accent-color:var(--gold);">
                            <span style="font-size:0.85rem;color:var(--text);">Server Saya ({{ auth()->user()->albion_server }})</span>
                        </label>
                        @endif
                    </div>
                    
                    <!-- Image Upload Button -->
                    <label for="imageInput" style="cursor:pointer;color:var(--text-muted);display:flex;align-items:center;gap:4px;padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-size:0.85rem;transition:color 0.2s,border-color 0.2s;">
                        <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Foto</span>
                    </label>
                    <input type="file" id="imageInput" name="image" accept="image/*" style="display:none;">
                    @error('image')
                    <div style="color:#ff6b6b;font-size:0.8rem;">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" style="background:var(--gold);color:var(--bg);border:none;padding:10px 24px;border-radius:8px;font-weight:600;font-size:0.9rem;cursor:pointer;transition:opacity 0.2s;">
                    Post
                </button>
            </div>
        </form>
    </div>
    @endauth

    @guest
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:24px;text-align:center;">
        <p style="color:var(--text-muted);margin-bottom:16px;">Login untuk berbagi status</p>
        <a href="{{ route('login') }}" style="display:inline-block;background:var(--gold);color:var(--bg);border:none;padding:10px 24px;border-radius:8px;font-weight:600;font-size:0.9rem;">
            Login
        </a>
    </div>
    @endguest

    <!-- Status Feed -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        @forelse($statuses as $status)
        <div class="status-card" style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;">
            <!-- Header -->
            <div style="display:flex;gap:12px;margin-bottom:16px;">
                <a href="{{ route('profile.show', $status->user) }}">
                    <img src="{{ $status->user->display_avatar }}" style="width:44px;height:44px;border-radius:50%;border:2px solid var(--border);">
                </a>
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px;">
                        <a href="{{ route('profile.show', $status->user) }}" style="font-weight:600;color:var(--text);font-size:0.95rem;">
                            {{ $status->user->display_name }}
                        </a>
                        @if($status->visibility === 'server')
                        <span style="background:var(--bg-panel);color:var(--text-muted);font-size:0.7rem;padding:2px 8px;border-radius:12px;font-weight:500;">
                            {{ $status->server }}
                        </span>
                        @endif
                    </div>
                    <div style="color:var(--text-muted);font-size:0.8rem;">
                        {{ $status->created_at->diffForHumans() }}
                    </div>
                </div>

                @auth
                @if(auth()->id() === $status->user_id || auth()->user()->role === 'admin')
                <form action="{{ route('social.destroy', $status) }}" method="POST" onsubmit="return confirm('Hapus status ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:4px;font-size:0.8rem;">
                        <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                        </svg>
                    </button>
                </form>
                @endif
                @endauth
            </div>

            <!-- Body -->
            <div style="color:var(--text);font-size:0.95rem;line-height:1.6;margin-bottom:16px;white-space:pre-wrap;">{{ $status->body }}</div>

            <!-- Image -->
            @if($status->image)
            <div style="margin-bottom:16px;">
                <img src="{{ asset('storage/' . $status->image) }}" alt="Status image" style="max-width:100%;border-radius:8px;border:1px solid var(--border);">
            </div>
            @endif

            <!-- Actions -->
            <div style="display:flex;align-items:center;gap:16px;padding-top:12px;border-top:1px solid var(--border);">
                @auth
                <button 
                    class="like-btn" 
                    data-status-id="{{ $status->id }}"
                    data-liked="{{ $status->isLikedBy(auth()->user()) ? 'true' : 'false' }}"
                    style="background:none;border:none;color:var(--text-muted);cursor:pointer;display:flex;align-items:center;gap:6px;font-size:0.85rem;transition:color 0.2s;"
                >
                    <svg class="like-icon" style="width:18px;height:18px;stroke:currentColor;fill:{{ $status->isLikedBy(auth()->user()) ? 'currentColor' : 'none' }};stroke-width:2;" viewBox="0 0 24 24">
                        <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                    </svg>
                    <span class="like-count">{{ $status->likes_count }}</span>
                </button>
                @else
                <div style="color:var(--text-muted);display:flex;align-items:center;gap:6px;font-size:0.85rem;">
                    <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                        <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                    </svg>
                    <span>{{ $status->likes_count }}</span>
                </div>
                @endauth

                <div style="color:var(--text-muted);display:flex;align-items:center;gap:6px;font-size:0.85rem;">
                    <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                    <span>{{ $status->comments_count }}</span>
                </div>
            </div>
        </div>
        @empty
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:48px 24px;text-align:center;">
            <p style="color:var(--text-muted);font-size:0.95rem;">No status yet. Be the first to post!</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($statuses->hasPages())
    <div style="margin-top:32px;display:flex;justify-content:center;">
        {{ $statuses->links() }}
    </div>
    @endif
</div>

@auth
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Like button functionality
    const likeButtons = document.querySelectorAll('.like-btn');
    
    likeButtons.forEach(btn => {
        btn.addEventListener('click', async function() {
            const statusId = this.dataset.statusId;
            const isLiked = this.dataset.liked === 'true';
            
            try {
                const response = await fetch(`/social/${statusId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update button state
                    this.dataset.liked = data.liked ? 'true' : 'false';
                    
                    // Update icon
                    const icon = this.querySelector('.like-icon');
                    icon.style.fill = data.liked ? 'currentColor' : 'none';
                    
                    // Update count
                    this.querySelector('.like-count').textContent = data.likes_count;
                    
                    // Update color
                    this.style.color = data.liked ? 'var(--gold)' : 'var(--text-muted)';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });

    // Image preview functionality
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removeImageBtn = document.getElementById('removeImage');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    removeImageBtn.addEventListener('click', function() {
        imageInput.value = '';
        imagePreview.style.display = 'none';
        previewImg.src = '';
    });
});
</script>
@endauth
@endsection
