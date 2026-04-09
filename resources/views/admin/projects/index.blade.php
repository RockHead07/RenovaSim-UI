@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

<!-- HEADER -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-semibold text-white">Projects</h2>

    <button class="bg-white text-black px-4 py-2 rounded-lg text-sm hover:opacity-90">
        + Add Project
    </button>
</div>

<!-- SEARCH + FILTER -->
<div class="bg-[#1a1a1a] border border-gray-800 rounded-xl p-4 mb-6">

    <div class="flex flex-col md:flex-row gap-4 md:items-center md:justify-between">

        <!-- Search -->
        <input type="text"
               placeholder="Search by name, user, or room..."
               class="w-full md:w-1/3 bg-[#121212] border border-gray-800 px-4 py-2 rounded-lg text-sm focus:outline-none focus:border-gray-600">

        <!-- Filter -->
        <div class="flex gap-2 text-sm">
            <button class="px-3 py-1 rounded-lg bg-green-700 text-white">All</button>
            <button class="px-3 py-1 rounded-lg bg-[#121212] hover:bg-gray-800">Draft</button>
            <button class="px-3 py-1 rounded-lg bg-[#121212] hover:bg-gray-800">Estimated</button>
            <button class="px-3 py-1 rounded-lg bg-[#121212] hover:bg-gray-800">Completed</button>
        </div>

    </div>

</div>

<!-- TABLE -->
<div class="bg-[#1a1a1a] border border-gray-800 rounded-xl overflow-hidden">

    <table class="w-full text-sm">

        <thead class="text-gray-500 border-b border-gray-800 bg-[#161616]">
            <tr>
                <th class="text-left py-3 px-4">ID</th>
                <th class="text-left px-4">Project</th>
                <th class="text-left px-4">User</th>
                <th class="text-center">Room</th>
                <th class="text-center">Area</th>
                <th class="text-center">Cost</th>
                <th class="text-center">Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            @foreach([
                ['#1','Kitchen Remodel','John Doe','Kitchen','25 m²','$12,400','Completed'],
                ['#2','Bathroom Update','Jane Smith','Bathroom','12 m²','$6,800','Estimated'],
                ['#3','Living Room','Ali Mammadov','Living','35 m²','—','Draft'],
                ['#4','Bedroom Reno','Sara Johnson','Bedroom','20 m²','$8,200','Completed'],
            ] as $p)

            <tr class="border-b border-gray-800 hover:bg-[#161616] transition">

                <td class="py-3 px-4 text-gray-400">{{ $p[0] }}</td>

                <td class="px-4 font-medium text-white">
                    {{ $p[1] }}
                </td>

                <td class="px-4 text-gray-400">
                    {{ $p[2] }}
                </td>

                <td class="text-center text-blue-400">
                    {{ $p[3] }}
                </td>

                <td class="text-center text-gray-400">
                    {{ $p[4] }}
                </td>

                <td class="text-center text-white">
                    {{ $p[5] }}
                </td>

                <!-- STATUS -->
                <td class="text-center">
                    <span class="px-2 py-1 rounded text-xs
                        {{ $p[6]=='Completed' ? 'bg-green-700 text-green-200' : '' }}
                        {{ $p[6]=='Estimated' ? 'bg-yellow-700 text-yellow-200' : '' }}
                        {{ $p[6]=='Draft' ? 'bg-gray-700 text-gray-300' : '' }}
                    ">
                        {{ $p[6] }}
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