@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

<!-- STATS -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

    <div class="bg-[#1a1a1a] p-5 rounded-xl border border-gray-800">
        <p class="text-xs text-gray-500">TOTAL USERS</p>
        <h2 class="text-2xl text-white mt-2">120</h2>
        <p class="text-green-500 text-xs mt-1">↑ 12% this month</p>
    </div>

    <div class="bg-[#1a1a1a] p-5 rounded-xl border border-gray-800">
        <p class="text-xs text-gray-500">PROJECTS</p>
        <h2 class="text-2xl text-white mt-2">67</h2>
        <p class="text-green-500 text-xs mt-1">↑ 8 new this week</p>
    </div>

    <div class="bg-[#1a1a1a] p-5 rounded-xl border border-gray-800">
        <p class="text-xs text-gray-500">MATERIALS</p>
        <h2 class="text-2xl text-white mt-2">240</h2>
        <p class="text-gray-500 text-xs mt-1">across 8 categories</p>
    </div>

    <div class="bg-[#1a1a1a] p-5 rounded-xl border border-gray-800">
        <p class="text-xs text-gray-500">ACTIVE PLANS</p>
        <h2 class="text-2xl text-white mt-2">3</h2>
        <p class="text-gray-500 text-xs mt-1">Free - Smart - Pro</p>
    </div>

</div>

<!-- RECENT USERS -->
<div class="bg-[#1a1a1a] rounded-xl border border-gray-800 p-5 mb-6">

    <div class="flex justify-between mb-4">
        <h3 class="text-white font-semibold">Recent Users</h3>
        <button class="bg-white text-black px-3 py-1 rounded text-sm">+ Add User</button>
    </div>

    <table class="w-full text-sm">
        <thead class="text-gray-500 border-b border-gray-800">
            <tr>
                <th class="text-left py-2">Name</th>
                <th class="text-left">Email</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-800">
                <td class="py-3 text-white">John Doe</td>
                <td>john@mail.com</td>
                <td><span class="bg-yellow-600 px-2 py-1 rounded text-xs">Pro</span></td>
                <td><span class="bg-green-700 px-2 py-1 rounded text-xs">Active</span></td>
                <td>
                    <button class="bg-gray-200 text-black px-2 py-1 rounded text-xs">Edit</button>
                    <button class="bg-red-600 px-2 py-1 rounded text-xs">Delete</button>
                </td>
            </tr>

            <tr class="border-b border-gray-800">
                <td class="py-3 text-white">Jane Smith</td>
                <td>jane@mail.com</td>
                <td><span class="bg-green-700 px-2 py-1 rounded text-xs">Smart</span></td>
                <td><span class="bg-green-700 px-2 py-1 rounded text-xs">Active</span></td>
                <td>
                    <button class="bg-gray-200 text-black px-2 py-1 rounded text-xs">Edit</button>
                    <button class="bg-red-600 px-2 py-1 rounded text-xs">Delete</button>
                </td>
            </tr>

        </tbody>
    </table>

</div>

<!-- BOTTOM GRID -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- PRICING -->
    <div class="bg-[#1a1a1a] rounded-xl border border-gray-800 p-5">

        <h3 class="text-white font-semibold mb-4">Pricing Plans</h3>

        <table class="w-full text-sm">
            <thead class="text-gray-500 border-b border-gray-800">
                <tr>
                    <th class="text-left py-2">Plan</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <tr class="border-b border-gray-800">
                    <td class="py-3 text-white">Free</td>
                    <td>$0/mo</td>
                    <td><span class="bg-green-700 px-2 py-1 rounded text-xs">Active</span></td>
                    <td><button class="bg-gray-200 text-black px-2 py-1 rounded text-xs">Edit</button></td>
                </tr>

                <tr class="border-b border-gray-800">
                    <td class="py-3 text-white">Smart</td>
                    <td>$19/mo</td>
                    <td><span class="bg-green-700 px-2 py-1 rounded text-xs">Active</span></td>
                    <td><button class="bg-gray-200 text-black px-2 py-1 rounded text-xs">Edit</button></td>
                </tr>

                <tr>
                    <td class="py-3 text-white">Pro</td>
                    <td>$49/mo</td>
                    <td><span class="bg-green-700 px-2 py-1 rounded text-xs">Active</span></td>
                    <td><button class="bg-gray-200 text-black px-2 py-1 rounded text-xs">Edit</button></td>
                </tr>
            </tbody>
        </table>

    </div>

    <!-- PARTNERS -->
    <div class="bg-[#1a1a1a] rounded-xl border border-gray-800 p-5">

        <h3 class="text-white font-semibold mb-4">Partners</h3>

        <table class="w-full text-sm">
            <thead class="text-gray-500 border-b border-gray-800">
                <tr>
                    <th class="text-left py-2">Name</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <tr class="border-b border-gray-800">
                    <td class="py-3 text-white">BuildMax</td>
                    <td class="text-center">1</td>
                    <td><span class="bg-green-700 px-2 py-1 rounded text-xs">Active</span></td>
                    <td><button class="bg-gray-200 text-black px-2 py-1 rounded text-xs">Edit</button></td>
                </tr>

                <tr class="border-b border-gray-800">
                    <td class="py-3 text-white">HomeReno Co</td>
                    <td class="text-center">2</td>
                    <td><span class="bg-green-700 px-2 py-1 rounded text-xs">Active</span></td>
                    <td><button class="bg-gray-200 text-black px-2 py-1 rounded text-xs">Edit</button></td>
                </tr>

                <tr>
                    <td class="py-3 text-white">DesignHub</td>
                    <td class="text-center">3</td>
                    <td><span class="bg-green-700 px-2 py-1 rounded text-xs">Active</span></td>
                    <td><button class="bg-gray-200 text-black px-2 py-1 rounded text-xs">Edit</button></td>
                </tr>
            </tbody>
        </table>

    </div>

</div>

@endsection