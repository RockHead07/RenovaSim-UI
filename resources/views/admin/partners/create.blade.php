@extends('admin.layout')

@section('title', 'Add Partner')

@section('content')

<div class="bg-[#1a1a1a] rounded-xl border border-gray-800 p-6 max-w-2xl">
    <h3 class="text-white font-semibold mb-6">Add New Partner</h3>

    <form action="{{ route('admin.partners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Partner Name -->
        <div class="mb-4">
            <label class="text-gray-400 text-sm block mb-2">Partner Name</label>
            <input type="text" name="name" class="w-full bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded" placeholder="e.g., IKEA" required>
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Logo Upload -->
        <div class="mb-4">
            <label class="text-gray-400 text-sm block mb-2">Partner Logo</label>
            <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml" class="w-full bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded">
            <p class="text-gray-500 text-xs mt-1">Accepted: PNG, JPG, SVG (Max 2MB)</p>
            @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Order -->
        <div class="mb-4">
            <label class="text-gray-400 text-sm block mb-2">Display Order</label>
            <select name="order" class="w-full bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded" required>
                <option value="">Select display order</option>
                @foreach ($availableOrders as $orderNum)
                    <option value="{{ $orderNum }}">Position {{ $orderNum }}</option>
                @endforeach
            </select>
            <p class="text-gray-500 text-xs mt-1">Available positions only</p>
            @error('order') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Status -->
        <div class="mb-6">
            <label class="text-gray-400 text-sm block mb-2">Status</label>
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded">
                <label for="is_active" class="text-gray-400 text-sm ml-2">Active</label>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button type="submit" class="bg-white text-black px-6 py-2 rounded font-medium">Add Partner</button>
            <a href="{{ route('admin.partners.index') }}" class="bg-gray-700 text-white px-6 py-2 rounded">Cancel</a>
        </div>
    </form>
</div>

@endsection
