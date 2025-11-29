@extends('layouts.admin')

@section('page-title', 'Genre Management')

@section('content')
    <style>
        .genre-management {
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.75rem;
        }

        .stat-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
        }

        .genre-table {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2rem;
        }

        .genre-row {
            display: grid;
            grid-template-columns: 50px 80px 1fr 150px 100px 100px 150px;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            align-items: center;
        }

        .genre-row:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .genre-icon {
            font-size: 2rem;
            text-align: center;
        }

        .genre-color {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 26px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 13px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .toggle-switch.active {
            background: #667eea;
        }

        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            background: #fff;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        .toggle-switch.active::after {
            transform: translateX(24px);
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
        }

        .btn-danger {
            background: #ef4444;
            color: #fff;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
    </style>

    <div class="genre-management">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1
                    style="font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, #fff 0%, #667eea 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 0.5rem;">
                    Genre Management
                </h1>
                <p style="color: rgba(255, 255, 255, 0.7); font-size: 1.125rem;">
                    Manage music genres for your platform
                </p>
            </div>
            <div>
                <a href="{{ route('admin.genres.create') }}" class="btn btn-primary">
                    ➕ Add New Genre
                </a>
                <button onclick="updateCounts()" class="btn"
                    style="background: rgba(255, 255, 255, 0.1); color: #fff; margin-left: 0.5rem;">
                    🔄 Update Counts
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total_genres'] }}</div>
                <div class="stat-label">Total Genres</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['active_genres'] }}</div>
                <div class="stat-label">Active Genres</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($stats['total_tracks']) }}</div>
                <div class="stat-label">Total Tracks</div>
            </div>
        </div>

        <!-- Genre Table -->
        <div class="genre-table">
            <div class="genre-row"
                style="font-weight: 700; color: rgba(255, 255, 255, 0.6); border-bottom: 2px solid rgba(255, 255, 255, 0.1);">
                <div>Order</div>
                <div>Icon</div>
                <div>Name</div>
                <div>Color</div>
                <div>Tracks</div>
                <div>Status</div>
                <div>Actions</div>
            </div>

            @forelse($genres as $genre)
                <div class="genre-row" data-id="{{ $genre->id }}">
                    <div style="color: rgba(255, 255, 255, 0.5);">#{{ $genre->sort_order }}</div>
                    <div class="genre-icon">{{ $genre->icon ?? '🎵' }}</div>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">{{ $genre->name }}</div>
                        <div style="font-size: 0.875rem; color: rgba(255, 255, 255, 0.5);">{{ $genre->description }}</div>
                    </div>
                    <div>
                        <div class="genre-color" style="background: {{ $genre->color ?? '#667eea' }};"></div>
                    </div>
                    <div style="font-weight: 600;">{{ number_format($genre->tracks_count) }}</div>
                    <div>
                        <div class="toggle-switch {{ $genre->is_active ? 'active' : '' }}"
                            onclick="toggleGenre({{ $genre->id }}, this)"></div>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="{{ route('admin.genres.edit', $genre) }}" class="btn"
                            style="background: rgba(102, 126, 234, 0.2); color: #667eea; padding: 0.5rem 0.75rem;">
                            ✏️ Edit
                        </a>
                        <button onclick="deleteGenre({{ $genre->id }})" class="btn btn-danger" style="padding: 0.5rem 0.75rem;">
                            🗑️
                        </button>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 3rem; color: rgba(255, 255, 255, 0.5);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎵</div>
                    <p>No genres found. Add your first genre!</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div style="margin-top: 2rem;">
            {{ $genres->links() }}
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleGenre(id, element) {
                fetch(`/admin/genres/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            element.classList.toggle('active');
                        }
                    });
            }

            function deleteGenre(id) {
                if (!confirm('Are you sure you want to delete this genre?')) return;

                fetch(`/admin/genres/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Error deleting genre');
                        }
                    });
            }

            function updateCounts() {
                window.location.href = '{{ route('admin.genres.update-counts') }}';
            }
        </script>
    @endpush
@endsection