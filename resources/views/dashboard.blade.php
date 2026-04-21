@extends('room.layout')

@section('title', 'Dashboard - RenovaSim')
@section('heading', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">
            Welcome back, {{ Auth::user()->first_name ?? Auth::user()->username }}! 👋
        </h1>
        <p class="text-gray-400">Here's what's happening with your RenovaSim projects</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Rooms -->
        <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm mb-2">Total Rooms</p>
                    <p class="text-3xl font-bold text-white">{{ Auth::user()->rooms()->count() }}</p>
                </div>
                <div class="text-4xl">📐</div>
            </div>
            <a href="{{ route('room.index') }}" class="text-blue-400 text-sm hover:text-blue-300 mt-4 inline-block">
                View all rooms →
            </a>
        </div>

        <!-- Total Projects -->
        <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm mb-2">Active Plan</p>
                    <p class="text-3xl font-bold text-white">{{ Auth::user()->plan ?? 'Free' }}</p>
                </div>
                <div class="text-4xl">💎</div>
            </div>
            <a href="#" class="text-blue-400 text-sm hover:text-blue-300 mt-4 inline-block">
                Upgrade plan →
            </a>
        </div>

        <!-- Account Status -->
        <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm mb-2">Account Status</p>
                    <p class="text-3xl font-bold text-green-400">{{ Auth::user()->account_status ?? 'Active' }}</p>
                </div>
                <div class="text-4xl">✅</div>
            </div>
            <a href="{{ route('profile.edit') }}" class="text-blue-400 text-sm hover:text-blue-300 mt-4 inline-block">
                View profile →
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('room.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg transition flex items-center gap-3">
                <span class="text-2xl">➕</span>
                <span class="font-semibold">Create New Room</span>
            </a>
            <a href="{{ route('room.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white p-4 rounded-lg transition flex items-center gap-3">
                <span class="text-2xl">📂</span>
                <span class="font-semibold">View All Rooms</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="bg-slate-700 hover:bg-slate-600 text-white p-4 rounded-lg transition flex items-center gap-3">
                <span class="text-2xl">⚙️</span>
                <span class="font-semibold">Settings</span>
            </a>
        </div>
    </div>

    <!-- Recent Rooms -->
    @if(Auth::user()->rooms()->count() > 0)
        <div>
            <h2 class="text-xl font-bold text-white mb-4">Recent Rooms</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach(Auth::user()->rooms()->latest()->take(3)->get() as $room)
                    <div class="bg-slate-800 rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition border border-slate-700">
                        <div class="h-48 bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-4xl mb-2">📐</div>
                                <div class="text-sm text-gray-400">
                                    {{ $room->width }}m × {{ $room->length }}m × {{ $room->height }}m
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-white mb-2">{{ $room->name }}</h3>
                            <p class="text-sm text-gray-400 mb-4">{{ $room->description ?? 'No description' }}</p>
                            <a href="{{ route('room.editor', $room) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-2 rounded transition">
                                Edit Room
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center py-12 bg-slate-800 rounded-lg border border-slate-700">
            <div class="text-6xl mb-4">📭</div>
            <p class="text-gray-400 mb-6">No rooms yet. Create your first room to get started!</p>
            <a href="{{ route('room.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                <span>➕</span>
                <span>Create First Room</span>
            </a>
        </div>
    @endif
</div>
@endsection
