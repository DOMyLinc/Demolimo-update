@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Audio Ads</h1>
            <a href="{{ route('ads.create') }}" class="btn btn-primary">Create Ad Campaign</a>
        </div>

        <div class="row">
            @forelse($ads as $ad)
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title">{{ $ad->title }}</h5>
                                <span
                                    class="badge badge-{{ $ad->status == 'active' ? 'success' : ($ad->status == 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($ad->status) }}
                                </span>
                            </div>
                            <p class="card-text">Budget: ${{ number_format($ad->budget, 2) }}</p>
                            <audio controls class="w-100 mt-2">
                                <source src="{{ Storage::url($ad->audio_path) }}" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="{{ route('ads.show', $ad->id) }}" class="btn btn-sm btn-outline-info">View Details</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">You haven't created any ad campaigns yet.</div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $ads->links() }}
        </div>
    </div>
@endsection