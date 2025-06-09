<section class="px-4 py-3 bg-white rounded-lg dark:bg-gray-800">
    <header class="mb-4">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6">
        @csrf
        @method('put')

        <!-- Current Password -->
        <div class="mb-4">
            <label for="update_password_current_password" class="block text-sm text-gray-700 dark:text-gray-400">
                <span>{{ __('Current Password') }}</span>
                <input id="update_password_current_password" name="current_password" type="password" 
                    class="block w-full mt-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" 
                    autocomplete="current-password" />
                @error('current_password', 'updatePassword')
                    <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                @enderror
            </label>
        </div>

        <!-- New Password -->
        <div class="mb-4">
            <label for="update_password_password" class="block text-sm text-gray-700 dark:text-gray-400">
                <span>{{ __('New Password') }}</span>
                <input id="update_password_password" name="password" type="password" 
                    class="block w-full mt-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" 
                    autocomplete="new-password" />
                @error('password', 'updatePassword')
                    <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                @enderror
            </label>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="update_password_password_confirmation" class="block text-sm text-gray-700 dark:text-gray-400">
                <span>{{ __('Confirm Password') }}</span>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                    class="block w-full mt-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" 
                    autocomplete="new-password" />
                @error('password_confirmation', 'updatePassword')
                    <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                @enderror
            </label>
        </div>

        <div class="flex mt-6 items-center">
            <button type="submit" 
                class="whitespace-nowrap inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="ml-3 text-sm text-green-600 dark:text-green-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
