<section class="px-4 py-3 bg-white rounded-lg dark:bg-gray-800">
    <header class="mb-4">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="whitespace-nowrap inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red"
    >
        {{ __('Delete Account') }}
    </button>

    <!-- Modal for confirmation -->
    <div
        x-data="{ show: false, focusables() { return [...$el.querySelectorAll('a, button, input, textarea, select, details, [tabindex]:not([tabindex=\'-1\'])')] }, firstFocusable() { return this.focusables()[0] }, lastFocusable() { return this.focusables()[this.focusables().length - 1] }, handleEscape(e) { if (e.key === 'Escape') { this.show = false } }, handleTab(e) { if (e.key === 'Tab') { if (e.shiftKey) { if (this.$el.contains(e.target) && e.target === this.firstFocusable()) { e.preventDefault(); this.lastFocusable().focus(); } } else { if (this.$el.contains(e.target) && e.target === this.lastFocusable()) { e.preventDefault(); this.firstFocusable().focus(); } } } } }"
        x-init="$watch('show', value => { if (value) { document.body.classList.add('overflow-y-hidden'); setTimeout(() => { $refs.content.focus(); }); } else { document.body.classList.remove('overflow-y-hidden'); } });"
        x-on:keydown.escape.window="show = false"
        x-on:keydown.tab.prevent="handleTab"
        x-on:open-modal.window="$event.detail === 'confirm-user-deletion' ? show = true : null"
        x-on:close.stop="show = false"
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-30 flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0"
    >
        <div
            x-on:click="show = false"
            class="fixed inset-0 transform transition-all bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-80"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>

        <div
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="w-full max-w-md px-6 py-4 mx-auto bg-white rounded-lg shadow-lg dark:bg-gray-800"
        >
            <form method="post" action="{{ route('profile.destroy') }}" class="p-4">
                @csrf
                @method('delete')

                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                    {{ __('Are you sure you want to delete your account?') }}
                </h2>

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>

                <div class="mb-4">
                    <label for="password" class="block text-sm text-gray-700 dark:text-gray-400">
                        <span class="sr-only">{{ __('Password') }}</span>
                        <input id="password" name="password" type="password" 
                            class="block w-full mt-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" 
                            placeholder="{{ __('Password') }}" />
                        @error('password', 'userDeletion')
                            <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                <div class="flex justify-end mt-6 space-x-4">
                    <button type="button" x-on:click="show = false"
                        class="px-4 py-2 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray">
                        {{ __('Cancel') }}
                    </button>

                    <button type="submit"
                        class="whitespace-nowrap inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red">
                        {{ __('Delete Account') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
