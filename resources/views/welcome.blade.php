@extends('layouts.marketing')

@section('content')
<div class="min-h-screen flex flex-col">
    <header class="bg-emerald-600 text-white">
        <div class="max-w-7xl mx-auto px-6 py-16 grid md:grid-cols-2 gap-8 items-center">
            <div>
                <h1 class="text-4xl font-bold mb-4">{{ config('app.name') }}</h1>
                <p class="mb-6">Manage recycling data easily and efficiently with our platform.</p>
                <div class="space-x-4">
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-white text-emerald-600 rounded font-semibold">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-4 py-2 border border-white rounded font-semibold">Register</a>
                    @endif
                </div>
            </div>
            <div class="text-center">
                <img src="https://via.placeholder.com/800x400?text=Screenshot" alt="App screenshot" class="mx-auto rounded shadow">
            </div>
        </div>
    </header>

    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-2xl font-bold text-center mb-12">Features</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-6 bg-white shadow rounded">
                    <h3 class="font-semibold mb-2">Easy Data Entry</h3>
                    <p class="text-sm text-gray-600">Quickly log recycling activity with intuitive forms.</p>
                </div>
                <div class="p-6 bg-white shadow rounded">
                    <h3 class="font-semibold mb-2">Reports</h3>
                    <p class="text-sm text-gray-600">Visualize your impact through detailed reports.</p>
                </div>
                <div class="p-6 bg-white shadow rounded">
                    <h3 class="font-semibold mb-2">Community Sharing</h3>
                    <p class="text-sm text-gray-600">Share successes and tips with other users.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl font-bold mb-4">Ready to get started?</h2>
            <div class="space-x-4">
                <a href="{{ route('login') }}" class="px-6 py-3 bg-emerald-600 text-white rounded font-semibold">Log in</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="px-6 py-3 border border-emerald-600 text-emerald-600 rounded font-semibold">Register</a>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
