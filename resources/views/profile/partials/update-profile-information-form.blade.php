<section class="px-4 py-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
    <header class="mb-4">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6">
        @csrf
        @method('patch')

        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="block text-sm text-gray-700 dark:text-gray-400">
                <span>{{ __('Name') }}</span>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                    class="block w-full mt-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" 
                />
                @error('name')
                    <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                @enderror
            </label>
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-sm text-gray-700 dark:text-gray-400">
                <span>{{ __('Email') }}</span>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                    class="block w-full mt-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" 
                />
                @error('email')
                    <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                @enderror
            </label>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-orange-600 dark:text-orange-400">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" 
                            class="underline text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 focus:outline-none">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex mt-6 items-center">
            <button type="submit" 
                class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'profile-updated')
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
