@props(['zone'])

@php
    $bannerService = app(\App\Services\BannerService::class);
    $banners = $bannerService->getBannersForZone($zone, auth()->user());
@endphp

@foreach($banners as $banner)
    <div class="banner banner-{{ $banner->type }} mb-3" data-banner-id="{{ $banner->id }}" data-zone="{{ $zone }}">
        @if($banner->type === 'image')
            <div class="banner-image">
                @if($banner->link)
                    <a href="{{ $banner->link }}" target="_blank" class="banner-link">
                        <img src="{{ $banner->content }}" alt="{{ $banner->title }}" class="img-fluid">
                    </a>
                @else
                    <img src="{{ $banner->content }}" alt="{{ $banner->title }}" class="img-fluid">
                @endif
            </div>
        @elseif($banner->type === 'audio')
            <div class="banner-audio p-3 bg-light rounded">
                <h6 class="mb-2">{{ $banner->title }}</h6>
                <audio controls src="{{ $banner->content }}" class="w-100"></audio>
                @if($banner->link)
                    <a href="{{ $banner->link }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                        Learn More
                    </a>
                @endif
            </div>
        @elseif($banner->type === 'text')
            <div class="banner-text p-3 bg-info text-white rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">{{ $banner->title }}</h6>
                        <div>{!! $banner->content !!}</div>
                    </div>
                    @if($banner->link)
                        <a href="{{ $banner->link }}" target="_blank" class="btn btn-sm btn-light">
                            View
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endforeach

@once
    <script>
        // Banner tracking script
        document.addEventListener('DOMContentLoaded', function () {
            // Track impressions using Intersection Observer
            const banners = document.querySelectorAll('.banner');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const banner = entry.target;
                        const bannerId = banner.dataset.bannerId;

                        // Record impression
                        fetch('/api/banners/impression', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ banner_id: bannerId })
                        });

                        // Stop observing after impression is recorded
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            banners.forEach(banner => observer.observe(banner));

            // Track clicks
            document.querySelectorAll('.banner a.banner-link, .banner a[href]').forEach(link => {
                link.addEventListener('click', function (e) {
                    const banner = this.closest('.banner');
                    const bannerId = banner.dataset.bannerId;

                    // Record click
                    fetch('/api/banners/click', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ banner_id: bannerId })
                    });
                });
            });
        });
    </script>
@endonce