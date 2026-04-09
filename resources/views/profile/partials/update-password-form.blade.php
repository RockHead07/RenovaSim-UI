<section>

    <!-- HEADER -->
    <header class="mb-4">
        <p class="mt-1 text-sm text-gray-400 leading-relaxed">
            Ensure your account is using a strong password to stay secure.
        </p>
    </header>

    <!-- FORM -->
    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <!-- CURRENT PASSWORD -->
        <div>
            <label class="block text-sm text-gray-400 mb-1">
                Current Password
            </label>

            <input type="password" name="current_password"
                class="w-full bg-[#121212] border border-gray-700 text-white rounded-lg px-4 py-2
                       focus:outline-none focus:border-green-600 focus:ring-1 focus:ring-green-600 transition">
        </div>

        <!-- NEW PASSWORD -->
        <div>
            <label class="block text-sm text-gray-400 mb-1">
                New Password
            </label>

            <input type="password" name="password"
                class="w-full bg-[#121212] border border-gray-700 text-white rounded-lg px-4 py-2
                       focus:outline-none focus:border-green-600 focus:ring-1 focus:ring-green-600 transition">
        </div>

        <!-- CONFIRM -->
        <div>
            <label class="block text-sm text-gray-400 mb-1">
                Confirm Password
            </label>

            <input type="password" name="password_confirmation"
                class="w-full bg-[#121212] border border-gray-700 text-white rounded-lg px-4 py-2
                       focus:outline-none focus:border-green-600 focus:ring-1 focus:ring-green-600 transition">
        </div>

        <!-- BUTTON -->
        <div class="pt-2">
            <button type="submit"
                class="bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                Save Changes
            </button>
        </div>

    </form>

</section>