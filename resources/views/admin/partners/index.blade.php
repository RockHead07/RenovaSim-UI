@extends('admin.layout')

@section('title', 'Partners')

@section('content')

<div class="bg-[#1a1a1a] rounded-xl border border-gray-800 p-5">

    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-white font-semibold">Partners</h3>
        <a href="{{ route('admin.partners.create') }}" class="bg-white text-black px-3 py-1 rounded text-sm hover:bg-gray-200">
            + Add Partner
        </a>
    </div>

    <!-- Messages -->
    @if (session('success'))
        <div class="bg-green-700/20 border border-green-500 text-green-300 px-4 py-2 rounded mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Table -->
    @if ($partners->isEmpty())
        <p class="text-gray-500 text-center py-8">No partners added yet. <a href="{{ route('admin.partners.create') }}" class="text-white underline">Add one now</a></p>
    @else
        <table class="w-full text-sm">
            <thead class="text-gray-500 border-b border-gray-800">
                <tr>
                    <th class="text-left py-2">Logo</th>
                    <th class="text-left py-2">Name</th>
                    <th class="text-center">Order</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody class="text-gray-300">
                @foreach ($partners as $partner)
                    <tr class="border-b border-gray-800">
                        <td class="py-3">
                            @if ($partner->logo)
                                @php
                                    // Check if logo is a full path (from storage) or just a filename (from public)
                                    $logoPath = str_contains($partner->logo, '/') 
                                        ? asset('storage/' . $partner->logo)
                                        : asset('images/partners/' . $partner->logo);
                                @endphp
                                <img src="{{ $logoPath }}" alt="{{ $partner->name }}" class="h-8 w-auto" onerror="this.style.display='none'">
                            @else
                                <span class="text-gray-500 text-xs">No logo</span>
                            @endif
                        </td>
                        <td class="py-3 text-white">{{ $partner->name }}</td>
                        <td class="text-center">{{ $partner->order }}</td>
                        <td class="text-center">
                            <span class="px-2 py-1 rounded text-xs {{ $partner->is_active ? 'bg-green-700 text-green-200' : 'bg-gray-700 text-gray-400' }}">
                                {{ $partner->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.partners.edit', $partner->id) }}" class="bg-gray-200 text-black px-2 py-1 rounded text-xs hover:bg-gray-300 inline-block">Edit</a>
                            <form action="{{ route('admin.partners.destroy', $partner->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this partner?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>

@endsection