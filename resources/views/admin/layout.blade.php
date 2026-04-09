<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('theme') === 'dark' }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <title>RenovaSim Admin</title>

    @vite('resources/css/app.css')

    <!-- Alpine JS -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Init Theme -->
    <script>
        if(localStorage.getItem('theme') === 'dark'){
            document.documentElement.classList.add('dark');
        }
    </script>
</head>

<body class="bg-white text-gray-800 dark:bg-[#1c1c1c] dark:text-gray-300">

<div class="flex h-screen overflow-hidden">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white dark:bg-[#1a1a1a] border-r border-gray-200 dark:border-gray-800 flex flex-col">

        <!-- Logo -->
        <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-gray-800 text-black dark:text-white font-semibold">
            RenovaSim
        </div>

        <!-- Menu -->
        <div class="flex-1 px-4 py-4 text-sm">

            <p class="text-gray-500 mb-2">OVERVIEW</p>

            <a href="/admin/dashboard"
               class="flex items-center gap-3 px-3 py-2 rounded-lg mb-1
               {{ request()->is('admin/dashboard*') ? 'bg-green-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                Dashboard
            </a>

            <p class="text-gray-500 mt-6 mb-2">MANAGE</p>

            <a href="/admin/users"
               class="block px-3 py-2 rounded
               {{ request()->is('admin/users*') ? 'bg-green-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                Users
            </a>

            <a href="/admin/projects"
               class="block px-3 py-2 rounded
               {{ request()->is('admin/projects*') ? 'bg-green-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                Projects
            </a>

            <a href="/admin/materials"
               class="block px-3 py-2 rounded
               {{ request()->is('admin/materials*') ? 'bg-green-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                Materials
            </a>

            <a href="/admin/pricing"
               class="block px-3 py-2 rounded
               {{ request()->is('admin/pricing*') ? 'bg-green-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                Pricing Plans
            </a>

            <a href="/admin/partners"
               class="block px-3 py-2 rounded
               {{ request()->is('admin/partners*') ? 'bg-green-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                Partners
            </a>

        </div>

    </aside>

    <!-- MAIN -->
    <div class="flex-1 flex flex-col">

        <!-- TOPBAR -->
        <header class="h-16 flex items-center justify-between px-6 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-[#1a1a1a]">

            <h2 class="text-lg font-semibold text-black dark:text-white">
                @yield('title', 'Dashboard')
            </h2>

            <div class="flex items-center gap-4">

                <!-- USER DROPDOWN -->
                @auth
                <div x-data="{ open: false }" class="relative">

                    <button @click="open = !open"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">

                        <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white text-sm">
                            {{ strtoupper(substr(Auth::user()->name,0,1)) }}
                        </div>

                        <div class="text-left">
                            <p class="text-sm text-black dark:text-white">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">Administrator</p>
                        </div>

                    </button>

                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-gray-800 rounded-lg shadow-lg overflow-hidden z-50">

                        <a href="{{ route('profile.edit') }}"
                           class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                            Profile Settings
                        </a>

                        <div class="border-t border-gray-200 dark:border-gray-800"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                                Logout
                            </button>
                        </form>

                    </div>

                </div>
                @endauth

            </div>

        </header>

        <!-- CONTENT -->
        <main class="flex-1 p-6 overflow-y-auto">
            @yield('content')
        </main>

    </div>

</div>

</body>
</html>