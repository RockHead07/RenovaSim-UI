@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

<div class="bg-[#1a1a1a] rounded-xl border border-gray-800 p-5">

    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-white font-semibold">Partners</h3>
        <button class="bg-white text-black px-3 py-1 rounded text-sm">
            + Add Partner
        </button>
    </div>

    <!-- Table -->
    <table class="w-full text-sm">
        <thead class="text-gray-500 border-b border-gray-800">
            <tr>
                <th class="text-left py-2">Name</th>
                <th>Order</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-800">
                <td class="py-3 text-white">BuildMax</td>
                <td class="text-center">1</td>
                <td>
                    <span class="bg-green-700 px-2 py-1 rounded text-xs">
                        Active
                    </span>
                </td>
                <td>
                    <button class="bg-gray-200 text-black px-2 py-1 rounded text-xs">
                        Edit
                    </button>
                </td>
            </tr>

            <tr class="border-b border-gray-800">
                <td class="py-3 text-white">HomeReno Co</td>
                <td class="text-center">2</td>
                <td>
                    <span class="bg-green-700 px-2 py-1 rounded text-xs">
                        Active
                    </span>
                </td>
                <td>
                    <button class="bg-gray-200 text-black px-2 py-1 rounded text-xs">
                        Edit
                    </button>
                </td>
            </tr>

            <tr>
                <td class="py-3 text-white">DesignHub</td>
                <td class="text-center">3</td>
                <td>
                    <span class="bg-green-700 px-2 py-1 rounded text-xs">
                        Active
                    </span>
                </td>
                <td>
                    <button class="bg-gray-200 text-black px-2 py-1 rounded text-xs">
                        Edit
                    </button>
                </td>
            </tr>

        </tbody>
    </table>

</div>

@endsection