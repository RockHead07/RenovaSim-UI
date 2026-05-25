@extends('room.layout')

@section('title', 'Create Room - RenovaSim')
@section('heading', 'Create New Room')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-slate-800 rounded-lg p-8 shadow-lg border border-slate-700">
        <form action="{{ route('room.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Room Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-200 mb-2">
                    Room Name
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Living Room"
                    value="{{ old('name') }}"
                    class="w-full rounded-lg border border-slate-600 bg-slate-700 px-4 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                />
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-200 mb-2">
                    Description
                </label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="Add a description for this room"
                    rows="3"
                    class="w-full rounded-lg border border-slate-600 bg-slate-700 px-4 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
                @error('description')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Room Dimensions -->
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="width" class="block text-sm font-medium text-gray-200 mb-2">
                        Width (m)
                    </label>
                    <input
                        type="number"
                        id="width"
                        name="width"
                        placeholder="4"
                        value="{{ old('width', 4) }}"
                        step="0.1"
                        min="1"
                        class="w-full rounded-lg border border-slate-600 bg-slate-700 px-4 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                    @error('width')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="length" class="block text-sm font-medium text-gray-200 mb-2">
                        Length (m)
                    </label>
                    <input
                        type="number"
                        id="length"
                        name="length"
                        placeholder="5"
                        value="{{ old('length', 5) }}"
                        step="0.1"
                        min="1"
                        class="w-full rounded-lg border border-slate-600 bg-slate-700 px-4 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                    @error('length')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="height" class="block text-sm font-medium text-gray-200 mb-2">
                        Height (m)
                    </label>
                    <input
                        type="number"
                        id="height"
                        name="height"
                        placeholder="3"
                        value="{{ old('height', 3) }}"
                        step="0.1"
                        min="1"
                        class="w-full rounded-lg border border-slate-600 bg-slate-700 px-4 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                    @error('height')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button
                    type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition"
                >
                    Create Room
                </button>
                <a
                    href="{{ route('room.index') }}"
                    class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-semibold py-3 rounded-lg transition text-center"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
