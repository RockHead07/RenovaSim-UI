@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

<!-- HEADER -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-semibold text-white">Users</h2>

    <button onclick="openAddModal()"
        class="bg-white text-black px-4 py-2 rounded-lg text-sm hover:opacity-90">
        + Add User
    </button>
</div>

<!-- SEARCH + FILTER -->
<form method="GET" class="bg-[#1a1a1a] border border-gray-800 rounded-xl p-4 mb-6">
    <div class="flex flex-col md:flex-row gap-4 md:items-center md:justify-between">

        <!-- SEARCH -->
        <div class="flex w-full md:w-1/3 gap-2">
            <input type="text" name="search"
                value="{{ request('search') }}"
                placeholder="Search username or email..."
                class="w-full bg-[#121212] border border-gray-800 px-4 py-2 rounded-lg text-sm text-white">

            <button type="submit"
                class="bg-white text-black px-4 py-2 rounded-lg text-sm">
                Search
            </button>
        </div>

        <!-- FILTER -->
        <div class="flex gap-2 text-sm">

            <a href="?plan=All&search={{ request('search') }}"
                class="px-3 py-1 rounded-lg
                {{ request('plan')=='All' || !request('plan') ? 'bg-green-700 text-white' : 'bg-[#121212]' }}">
                All
            </a>

            <a href="?plan=Free&search={{ request('search') }}"
                class="px-3 py-1 rounded-lg
                {{ request('plan')=='Free' ? 'bg-gray-700 text-white' : 'bg-[#121212]' }}">
                Free
            </a>

            <a href="?plan=Smart&search={{ request('search') }}"
                class="px-3 py-1 rounded-lg
                {{ request('plan')=='Smart' ? 'bg-green-700 text-white' : 'bg-[#121212]' }}">
                Smart
            </a>

            <a href="?plan=Pro&search={{ request('search') }}"
                class="px-3 py-1 rounded-lg
                {{ request('plan')=='Pro' ? 'bg-yellow-600 text-white' : 'bg-[#121212]' }}">
                Pro
            </a>

        </div>

    </div>
</form>

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

        @forelse($users as $user)

            <tr class="border-b border-gray-800 hover:bg-[#161616]">
                <td class="py-3 px-4">#{{ $user->id }}</td>

                <td class="px-4 text-white font-medium">
                    {{ $user->username }}
                </td>

                <td class="px-4 text-gray-400">
                    {{ $user->email }}
                </td>

                <!-- PLAN BADGE -->
                <td class="text-center">
                    <span class="
                        px-2 py-1 rounded text-xs
                        {{ $user->plan=='Pro' ? 'bg-yellow-600' : '' }}
                        {{ $user->plan=='Smart' ? 'bg-green-700' : '' }}
                        {{ $user->plan=='Free' ? 'bg-gray-700' : '' }}
                    ">
                        {{ $user->plan ?? 'Free' }}
                    </span>
                </td>

                <td class="text-center text-gray-400">
                    {{ $user->created_at->format('Y-m-d') }}
                </td>

                <td class="text-center">
                    <span class="bg-green-700 px-2 py-1 rounded text-xs">
                        Active
                    </span>
                </td>

                <td class="text-center space-x-1">

                    <!-- EDIT -->
                    <button onclick='openEditModal(@json($user))'
                        class="bg-gray-200 text-black px-2 py-1 rounded text-xs">
                        Edit
                    </button>

                    <!-- DELETE -->
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')

                        <button onclick="return confirm('Delete this user?')"
                            class="bg-red-600 px-2 py-1 rounded text-xs">
                            Delete
                        </button>
                    </form>

                </td>
            </tr>

        @empty
            <tr>
                <td colspan="7" class="text-center py-6 text-gray-500">
                    No users found
                </td>
            </tr>
        @endforelse

        </tbody>
    </table>
</div>

<!-- PAGINATION -->
<div class="flex justify-center mt-6">
    {{ $users->links() }}
</div>

<!-- MODAL ADD USER -->
<div id="addModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50">
    <div class="bg-[#1a1a1a] p-6 rounded-xl w-full max-w-sm border border-gray-800">

        <h3 class="text-white mb-4">Add User</h3>

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <input type="text" name="username" placeholder="Username"
                class="w-full mb-3 bg-[#121212] border border-gray-800 px-3 py-2 rounded text-white" required>

            <input type="email" name="email" placeholder="Email"
                class="w-full mb-3 bg-[#121212] border border-gray-800 px-3 py-2 rounded text-white" required>

            <input type="password" name="password" placeholder="Password"
                class="w-full mb-4 bg-[#121212] border border-gray-800 px-3 py-2 rounded text-white" required>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeAddModal()"
                    class="px-3 py-1 bg-gray-700 rounded text-sm">
                    Cancel
                </button>

                <button type="submit"
                    class="px-3 py-1 bg-white text-black rounded text-sm">
                    Save
                </button>
            </div>

        </form>
    </div>
</div>

<!-- MODAL EDIT PLAN -->
<div id="editModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50">
    <div class="bg-[#1a1a1a] p-6 rounded-xl w-full max-w-sm border border-gray-800">

        <h3 class="text-white mb-4">Update Plan</h3>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT')

            <select name="plan" id="editPlan"
                class="w-full mb-4 bg-[#121212] border border-gray-800 px-3 py-2 rounded text-white">
                <option value="Free">Free</option>
                <option value="Smart">Smart</option>
                <option value="Pro">Pro</option>
            </select>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()"
                    class="px-3 py-1 bg-gray-700 rounded text-sm">
                    Cancel
                </button>

                <button type="submit"
                    class="px-3 py-1 bg-white text-black rounded text-sm">
                    Save
                </button>
            </div>

        </form>
    </div>
</div>

<!-- JS -->
<script>
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.getElementById('addModal').classList.add('flex');
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}

function openEditModal(user) {
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');

    document.getElementById('editPlan').value = user.plan ?? 'Free';
    document.getElementById('editForm').action = `/admin/users/${user.id}`;
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

@endsection