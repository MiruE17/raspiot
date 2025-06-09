<div x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-20 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center">
</div>

<aside class="fixed inset-y-0 z-30 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white dark:bg-gray-800 md:hidden"
       x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150"
       x-transition:enter-start="opacity-0 transform -translate-x-20" x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0 transform -translate-x-20" @click.away="closeSideMenu"
       @keydown.escape="closeSideMenu">
    <div class="py-4 text-gray-500 dark:text-gray-400">
        <!-- Logo dengan link ke home user -->
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
                <span class="{{ request()->routeIs('tokens.*') ? 'absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg' : '' }}" aria-hidden="true"></span>
                <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 {{ request()->routeIs('tokens.*') ? 'text-gray-800 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400' }} hover:text-gray-800 dark:hover:text-gray-200"
                   href="{{ route('tokens') }}">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <span class="ml-4">API Token</span>
                </a>
            </li>
            <!-- Add this item to your user sidebar navigation -->
            <li class="relative px-6 py-3">
                <span class="{{ request()->routeIs('user.schemes*') ? 'absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg' : '' }}" aria-hidden="true"></span>
                <a href="{{ route('user.schemes') }}" class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('user.schemes*') ? 'text-gray-800 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400' }}">
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