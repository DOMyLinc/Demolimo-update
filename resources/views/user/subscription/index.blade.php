<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Subscription Plans') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900">Choose Your Plan</h1>
                <p class="mt-4 text-xl text-gray-600">Unlock the full potential of your music career.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($plans as $plan)
                    <div
                        class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                        <div class="p-8">
                            <h3 class="text-2xl font-bold text-gray-900">{{ $plan->name }}</h3>
                            <div class="mt-4 flex items-baseline text-gray-900">
                                <span class="text-5xl font-extrabold tracking-tight">${{ $plan->price_monthly }}</span>
                                <span class="ml-1 text-xl font-semibold text-gray-500">/month</span>
                            </div>
                            <p class="mt-5 text-gray-500">{{ $plan->description ?? 'Perfect for getting started.' }}</p>

                            <ul class="mt-6 space-y-4">
                                @foreach ($plan->features as $feature)
                                    <li class="flex">
                                        <svg class="flex-shrink-0 w-6 h-6 text-green-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="ml-3 text-gray-500">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="p-8 bg-gray-50 border-t border-gray-200">
                            <form action="{{ route('user.subscription.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit"
                                    class="w-full bg-blue-600 text-white rounded-md py-3 font-bold hover:bg-blue-700 transition">
                                    {{ $plan->price_monthly > 0 ? 'Subscribe Now' : 'Current Plan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>