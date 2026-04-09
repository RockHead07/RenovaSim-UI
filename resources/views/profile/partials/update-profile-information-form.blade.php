<section>

    <!-- HEADER -->
    <header class="mb-4">
        <p class="mt-1 text-sm text-gray-400 leading-relaxed">
            Update your account's profile information and email address.
        </p>
    </header>

    <!-- FORM -->
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <!-- NAME -->
        <div>
            <label class="block text-sm text-gray-400 mb-1">
                Name
            </label>

            <input type="text" name="name"
                value="{{ old('name', $user->name) }}"
                class="w-full bg-[#121212] border border-gray-700 text-white rounded-lg px-4 py-2
                       focus:outline-none focus:border-green-600 focus:ring-1 focus:ring-green-600 transition">
        </div>

        <!-- EMAIL -->
        <div>
            <label class="block text-sm text-gray-400 mb-1">
                Email
            </label>

            <input type="email" name="email"
                value="{{ old('email', $user->email) }}"
                class="w-full bg-[#121212] border border-gray-700 text-white rounded-lg px-4 py-2
                       focus:outline-none focus:border-green-600 focus:ring-1 focus:ring-green-600 transition">

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="text-sm mt-2 text-yellow-400">
                    Your email address is unverified.

                    <button form="send-verification"
                        class="underline text-yellow-300 hover:text-yellow-200 ml-1">
                        Re-send verification email
                    </button>
                </p>
            @endif
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