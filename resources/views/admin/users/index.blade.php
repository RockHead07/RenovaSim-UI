@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

<!-- HEADER -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-semibold text-white">Users</h2>

    <button class="bg-white text-black px-4 py-2 rounded-lg text-sm hover:opacity-90">
        + Add User
    </button>
</div>

<!-- SEARCH + FILTER -->
<div class="bg-[#1a1a1a] border border-gray-800 rounded-xl p-4 mb-6">

    <div class="flex flex-col md:flex-row gap-4 md:items-center md:justify-between">

        <!-- Search -->
        <input type="text"
               placeholder="Search by name or email..."
               class="w-full md:w-1/3 bg-[#121212] border border-gray-800 px-4 py-2 rounded-lg text-sm focus:outline-none focus:border-gray-600">

        <!-- Filter -->
        <div class="flex gap-2 text-sm">
            <button class="px-3 py-1 rounded-lg bg-green-700 text-white">All</button>
            <button class="px-3 py-1 rounded-lg bg-[#121212] hover:bg-gray-800">Free</button>
            <button class="px-3 py-1 rounded-lg bg-[#121212] hover:bg-gray-800">Smart</button>
            <button class="px-3 py-1 rounded-lg bg-[#121212] hover:bg-gray-800">Pro</button>
        </div>

    </div>

</div>

<!-- TABLE -->
<div class="bg-[#1a1a1a] border border-gray-800 rounded-xl overflow-hidden">

    <table class="w-full text-sm">

        <thead class="text-gray-500 border-b border-gray-800 bg-[#161616]">
            <tr>
                <th class="text-left py-3 px-4">ID</th>
                <th class="text-left px-4">Name</th>
                <th class="text-left px-4">Email</th>
                <th class="text-center">Plan</th>
                <th class="text-center">Joined</th>
                <th class="text-center">Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            @foreach([
                ['#1','John Doe','john@mail.com','Pro','2024-01-15'],
                ['#2','Jane Smith','jane@mail.com','Smart','2024-02-20'],
                ['#3','Ali Mammadov','ali@mail.com','Free','2024-03-10'],
            ] as $user)

            <tr class="border-b border-gray-800 hover:bg-[#161616] transition">
                <td class="py-3 px-4">{{ $user[0] }}</td>

                <td class="px-4 font-medium text-white">
                    {{ $user[1] }}
                </td>

                <td class="px-4 text-gray-400">
                    {{ $user[2] }}
                </td>

                <!-- PLAN -->
                <td class="text-center">
                    <span class="
                        px-2 py-1 rounded text-xs
                        {{ $user[3]=='Pro' ? 'bg-yellow-600' : '' }}
                        {{ $user[3]=='Smart' ? 'bg-green-700' : '' }}
                        {{ $user[3]=='Free' ? 'bg-gray-700' : '' }}
                    ">
                        {{ $user[3] }}
                    </span>
                </td>

                <td class="text-center text-gray-400">
                    {{ $user[4] }}
                </td>

                <!-- STATUS -->
                <td class="text-center">
                    <span class="bg-green-700 px-2 py-1 rounded text-xs">
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

<!-- PAGINATION -->
<div class="flex justify-center mt-6 gap-2">

    <button class="w-8 h-8 bg-green-700 rounded-lg text-sm">1</button>

    <button class="w-8 h-8 bg-[#1a1a1a] border border-gray-800 rounded-lg text-sm hover:bg-gray-800">
        2
    </button>

</div>

@endsection