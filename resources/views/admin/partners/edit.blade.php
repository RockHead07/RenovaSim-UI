@extends('admin.layout')

@section('title', 'Edit Partner')

@section('content')

<div class="bg-[#1a1a1a] rounded-xl border border-gray-800 p-6 max-w-2xl">
    <h3 class="text-white font-semibold mb-6">Edit Partner</h3>

    <form action="{{ route('admin.partners.update', $partner->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Partner Name -->
        <div class="mb-4">
            <label class="text-gray-400 text-sm block mb-2">Partner Name</label>
            <input type="text" name="name" class="w-full bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded" value="{{ $partner->name }}" required>
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Current Logo -->
        @if ($partner->logo)
        <div class="mb-4">
            <label class="text-gray-400 text-sm block mb-2">Current Logo</label>
            @php
                // Check if logo is a full path (from storage) or just a filename (from public)
                $logoPath = str_contains($partner->logo, '/') 
                    ? asset('storage/' . $partner->logo)
                    : asset('images/partners/' . $partner->logo);
            @endphp
            <img src="{{ $logoPath }}" alt="{{ $partner->name }}" class="h-12 w-auto mb-2" onerror="this.style.display='none'">
        </div>
        @endif

        <!-- Logo Upload -->
        <div class="mb-4">
            <label class="text-gray-400 text-sm block mb-2">Change Logo</label>
            <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml" class="w-full bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded">
            <p class="text-gray-500 text-xs mt-1">Accepted: PNG, JPG, SVG (Max 2MB)</p>
            @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Order -->
        <div class="mb-4">
            <label class="text-gray-400 text-sm block mb-2">Display Order</label>
            <select name="order" class="w-full bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded" required>
                @foreach ($availableOrders as $orderNum)
                    <option value="{{ $orderNum }}" {{ $partner->order === $orderNum ? 'selected' : '' }}>
                        Position {{ $orderNum }}
                    </option>
                @endforeach
            </select>
            <p class="text-gray-500 text-xs mt-1">Available positions only</p>
            @error('order') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Status -->
        <div class="mb-6">
            <label class="text-gray-400 text-sm block mb-2">Status</label>
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $partner->is_active ? 'checked' : '' }} class="rounded">
                <label for="is_active" class="text-gray-400 text-sm ml-2">Active</label>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button type="submit" class="bg-white text-black px-6 py-2 rounded font-medium">Update Partner</button>
            <a href="{{ route('admin.partners.index') }}" class="bg-gray-700 text-white px-6 py-2 rounded">Cancel</a>
            <form action="{{ route('admin.partners.destroy', $partner->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this partner?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded">Delete</button>
            </form>
        </div>
    </form>
</div>

@endsection
