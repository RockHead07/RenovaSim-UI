@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

<!-- HEADER -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-semibold text-white">Materials</h2>

    <button class="bg-white text-black px-4 py-2 rounded-lg text-sm hover:opacity-90">
        + Add Material
    </button>
</div>

<!-- TABLE -->
<div class="bg-[#1a1a1a] border border-gray-800 rounded-xl overflow-hidden">

    <table class="w-full text-sm">

        <thead class="text-gray-500 border-b border-gray-800 bg-[#161616]">
            <tr>
                <th class="text-left py-3 px-4">ID</th>
                <th class="text-left px-4">Name</th>
                <th class="text-left px-4">Category</th>
                <th class="text-center">Price</th>
                <th class="text-center">Unit</th>
                <th class="text-center">Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            @foreach([
                ['#1','Ceramic Tile','Flooring','$25','m²'],
                ['#2','Oak Hardwood','Flooring','$45','m²'],
                ['#3','Granite Countertop','Surfaces','$80','m²'],
                ['#4','LED Panel Light','Lighting','$35','piece'],
                ['#5','Latex Paint','Paint','$12','liter'],
            ] as $m)

            <tr class="border-b border-gray-800 hover:bg-[#161616] transition">

                <td class="py-3 px-4 text-gray-400">{{ $m[0] }}</td>

                <td class="px-4 font-medium text-white">
                    {{ $m[1] }}
                </td>

                <td class="px-4 text-gray-400">
                    {{ $m[2] }}
                </td>

                <td class="text-center text-white">
                    {{ $m[3] }}
                </td>

                <td class="text-center text-gray-400">
                    {{ $m[4] }}
                </td>

                <!-- STATUS -->
                <td class="text-center">
                    <span class="bg-green-700 text-green-200 px-2 py-1 rounded text-xs">
                        Active
                    </span>
                </td>

                <!-- ACTION -->
                <td class="text-center space-x-1">
                    <button class="bg-gray-200 text-black px-2 py-1 rounded text-xs hover:opacity-80">
                        Edit
                    </button>
                    <button class="bg-red-600 px-2 py-1 rounded text-xs hover:bg-red-500">
                        Delete
                    </button>
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection