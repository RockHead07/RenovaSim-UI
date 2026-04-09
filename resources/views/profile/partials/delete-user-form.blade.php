<section class="space-y-6">

    <header>
        <p class="mt-1 text-sm text-gray-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg text-sm"
    >
        Delete Account
    </button>

    <!-- MODAL -->
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>

        <form method="post" action="{{ route('profile.destroy') }}"
              class="p-6 bg-[#1a1a1a] border border-gray-800 rounded-xl">

            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-white">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>

            <div class="mt-4">
                <x-input-label for="password" value="{{ __('Password') }}" />

                <x-text-input id="password" name="password" type="password"
                    class="mt-1 block w-full bg-[#121212] border border-gray-800 text-white rounded-lg"
                    placeholder="Password" />
            </div>

            <div class="mt-6 flex justify-end gap-2">

                <button x-on:click="$dispatch('close')"
                        type="button"
                        class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">
                    Cancel
                </button>

                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500">
                    Delete
                </button>

            </div>

        </form>

    </x-modal>

</section>