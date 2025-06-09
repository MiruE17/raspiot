<aside class="z-20 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block flex-shrink-0">
    <div class="py-4 text-gray-500 dark:text-gray-400">
        <a class="flex items-center px-6 my-2" href="{{ route('home') }}">
            <img src="{{ asset('images/raspiot_logo_for_dark.png') }}" alt="RaspIoT Logo" style="width:70%" class="h-auto">
        </a>
        <ul class="mt-6">
            <li class="relative px-6 py-3">
                <span class="{{ request()->routeIs('home') ? 'absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg' : '' }}" 
                      aria-hidden="true"></span>
                <a class="inline-flex items-center w-full text-sm font-semibold {{ request()->routeIs('home') ? 'text-gray-800 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400' }} hover:text-gray-800 dark:hover:text-gray-200"
                   href="{{ route('home') }}">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="ml-4">Home</span>
                </a>
            </li>
            <li class="relative px-6 py-3">
                <span class="{{ request()->routeIs('tokens') ? 'absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg' : '' }}" 
                      aria-hidden="true"></span>
                <a class="inline-flex items-center w-full text-sm font-semibold {{ request()->routeIs('tokens') ? 'text-gray-800 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400' }} hover:text-gray-800 dark:hover:text-gray-200"
                   href="{{ route('tokens') }}">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <span class="ml-4">API Token</span>
                </a>
            </li>
            <li class="relative px-6 py-3">
                <span class="{{ request()->routeIs('user.schemes*') || request()->routeIs('scheme.show') ? 'absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg' : '' }}" aria-hidden="true"></span>
                <a href="{{ route('user.schemes') }}" class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('user.schemes*') || request()->routeIs('scheme.show') ? 'text-gray-800 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="ml-4">Schemes</span>
                </a>
            </li>
            <!-- Menu lain akan ditambahkan di sini nanti -->
        </ul>
    </div>
</aside>