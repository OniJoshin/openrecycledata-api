<x-guest-layout>
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-r from-emerald-100 via-lime-100 to-emerald-50 py-12 px-6">
        <div class="text-center max-w-xl p-8 bg-white shadow rounded-lg">
            <div class="mb-6">
                <x-application-logo class="w-24 h-24 mx-auto text-emerald-600" />
            </div>
            <h1 class="text-4xl font-bold mb-4 text-gray-800">Welcome to {{ config('app.name', 'Laravel') }}</h1>
            <p class="text-gray-600 mb-8">Manage recycling data easily and efficiently with our platform.</p>
            @if (Route::has('login'))
                <div class="space-x-4">
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-4 py-2 border border-emerald-600 text-emerald-600 rounded hover:bg-emerald-50">Register</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-guest-layout>
