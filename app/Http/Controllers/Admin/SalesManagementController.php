<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrackSale;
use App\Models\AlbumSale;
use Illuminate\Http\Request;

class SalesManagementController extends Controller
{
    // Track Sales
    public function trackSales()
    {
        $sales = TrackSale::with(['track', 'buyer', 'seller', 'transaction'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_sales' => TrackSale::count(),
            'total_revenue' => TrackSale::sum('price'),
            'total_platform_fees' => TrackSale::sum('platform_fee'),
            'total_seller_earnings' => TrackSale::sum('seller_earnings'),
            'this_month_sales' => TrackSale::whereMonth('created_at', now()->month)->count(),
            'this_month_revenue' => TrackSale::whereMonth('created_at', now()->month)->sum('price'),
        ];

        return view('admin.sales.track_sales', compact('sales', 'stats'));
    }

    public function showTrackSale(TrackSale $sale)
    {
        $sale->load(['track', 'buyer', 'seller', 'transaction']);

        return view('admin.sales.track_sale_show', compact('sale'));
    }

    // Album Sales
    public function albumSales()
    {
        $sales = AlbumSale::with(['album', 'buyer', 'seller', 'transaction'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_sales' => AlbumSale::count(),
            'total_revenue' => AlbumSale::sum('price'),
            'total_platform_fees' => AlbumSale::sum('platform_fee'),
            'total_seller_earnings' => AlbumSale::sum('seller_earnings'),
            'this_month_sales' => AlbumSale::whereMonth('created_at', now()->month)->count(),
            'this_month_revenue' => AlbumSale::whereMonth('created_at', now()->month)->sum('price'),
        ];

        return view('admin.sales.album_sales', compact('sales', 'stats'));
    }

    public function showAlbumSale(AlbumSale $sale)
    {
        $sale->load(['album', 'buyer', 'seller', 'transaction']);

        return view('admin.sales.album_sale_show', compact('sale'));
    }

    // Combined Sales Analytics
    public function analytics()
    {
        $trackStats = [
            'total_sales' => TrackSale::count(),
            'total_revenue' => TrackSale::sum('price'),
            'platform_fees' => TrackSale::sum('platform_fee'),
            'seller_earnings' => TrackSale::sum('seller_earnings'),
        ];

        $albumStats = [
            'total_sales' => AlbumSale::count(),
            'total_revenue' => AlbumSale::sum('price'),
            'platform_fees' => AlbumSale::sum('platform_fee'),
            'seller_earnings' => AlbumSale::sum('seller_earnings'),
        ];

        $topSellingTracks = TrackSale::selectRaw('track_id, COUNT(*) as sales_count, SUM(price) as total_revenue')
            ->groupBy('track_id')
            ->orderByDesc('sales_count')
            ->with('track')
            ->limit(10)
            ->get();

        $topSellingAlbums = AlbumSale::selectRaw('album_id, COUNT(*) as sales_count, SUM(price) as total_revenue')
            ->groupBy('album_id')
            ->orderByDesc('sales_count')
            ->with('album')
            ->limit(10)
            ->get();

        $topSellers = TrackSale::selectRaw('seller_id, COUNT(*) as sales_count, SUM(seller_earnings) as total_earnings')
            ->groupBy('seller_id')
            ->orderByDesc('total_earnings')
            ->with('seller')
            ->limit(10)
            ->get();

        return view('admin.sales.analytics', compact(
            'trackStats',
            'albumStats',
            'topSellingTracks',
            'topSellingAlbums',
            'topSellers'
        ));
    }
}
