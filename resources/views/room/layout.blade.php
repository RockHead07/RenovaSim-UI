<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RenovaSim - Room Editor')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>
<body class="bg-background text-foreground font-sans">
    <!-- Navbar -->
    <nav class="bg-slate-900 border-b border-slate-700 p-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="hover:opacity-80 transition-opacity flex items-center">
                    <img src="{{ asset('images/logo.svg') }}" alt="RenovaSim" class="h-7 w-auto object-contain">
                </a>
                <span class="text-gray-400">|</span>
                <h1 class="text-lg font-semibold text-gray-100">@yield('heading')</h1>
                
                <!-- Navigation Links -->
                <div class="flex items-center gap-4 ml-4">
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-300 hover:text-white transition">Dashboard</a>
                    <a href="{{ route('room.index') }}" class="text-sm text-gray-300 hover:text-white transition">My Rooms</a>
                    @if(Auth::user()->is_admin || Auth::user()->email === 'admin@gmail.com')
                        <a href="{{ route('dashboard', ['admin' => true]) }}" class="text-sm text-blue-400 hover:text-blue-300 transition">Admin Panel</a>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-300">{{ Auth::user()->first_name ? Auth::user()->first_name . ' ' . Auth::user()->last_name : Auth::user()->username ?? Auth::user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-gray-300 hover:text-white transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
