@extends('room.layout')

@section('title', 'My Rooms - RenovaSim')
@section('heading', 'My Rooms')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Create New Room Button -->
    <div class="mb-8">
        <a href="{{ route('room.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
            <span>➕</span>
            <span>Create New Room</span>
        </a>
    </div>

    <!-- Rooms Grid -->
    @if($rooms->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($rooms as $room)
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
                        <p class="text-xs text-gray-500 mb-4">
                            {{ $room->objects_count ?? 0 }} objects
                        </p>
                        <a href="{{ route('room.editor', $room) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-2 rounded transition">
                            Edit
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
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
