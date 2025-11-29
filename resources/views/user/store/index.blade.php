@extends('layouts.app')

@section('title', 'Store')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold mb-2">Artist Merch Store</h1>
            <p class="text-gray-400">Shop exclusive merchandise from your favorite artists.</p>
        </div>
        <a href="{{ route('store.cart') }}" class="px-6 py-2 bg-white/10 hover:bg-white/20 text-white rounded-full font-bold transition relative">
            <i class="fas fa-shopping-cart mr-2"></i> Cart
            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">0</span>
        </a>
    </div>

    <!-- Categories -->
    <div class="mb-8">
        <div class="flex gap-3 overflow-x-auto pb-2">
            <a href="{{ route('store.index') }}" class="px-6 py-2 bg-purple-600 text-white rounded-full font-bold whitespace-nowrap">All</a>
            @foreach($categories as $cat)
                <a href="{{ route('store.category', $cat) }}" class="px-6 py-2 bg-white/10 hover:bg-white/20 text-white rounded-full font-bold whitespace-nowrap transition">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Featured Products -->
    @if($featuredProducts->isNotEmpty())
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6">Featured Products</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($featuredProducts as $product)
                    <a href="{{ route('store.show', $product) }}" class="group">
                        <div class="aspect-square mb-3 rounded-xl overflow-hidden bg-white/5 shadow-lg relative">
                            @if($product->images)
                                <img src="{{ Storage::url($product->images[0]) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center">
                                    <i class="fas fa-tshirt text-6xl text-white/20"></i>
                                </div>
                            @endif
                            <div class="absolute top-2 left-2 bg-yellow-600 text-white text-xs font-bold px-2 py-1 rounded">FEATURED</div>
                        </div>
                        <h3 class="font-bold truncate group-hover:text-purple-400 transition">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-400 truncate">{{ $product->user->name }}</p>
                        <p class="text-green-400 font-bold mt-1">${{ number_format($product->active_price, 2) }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <!-- New Products -->
    <section>
        <h2 class="text-2xl font-bold mb-6">New Arrivals</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
            @foreach($newProducts as $product)
                <a href="{{ route('store.show', $product) }}" class="group">
                    <div class="aspect-square mb-3 rounded-xl overflow-hidden bg-white/5 shadow-lg">
                        @if($product->images)
                            <img src="{{ Storage::url($product->images[0]) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center">
                                <i class="fas fa-box text-4xl text-white/20"></i>
                            </div>
                        @endif
                    </div>
                    <h3 class="font-bold text-sm truncate group-hover:text-purple-400 transition">{{ $product->name }}</h3>
                    <p class="text-xs text-gray-400 truncate">{{ $product->user->name }}</p>
                    <p class="text-green-400 font-bold text-sm mt-1">${{ number_format($product->active_price, 2) }}</p>
                </a>
            @endforeach
        </div>
    </section>
</div>
@endsection
