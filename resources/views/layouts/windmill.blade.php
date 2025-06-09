<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'RaspIoT') }}</title>

    <!-- Teknik preload CSS kritis -->
    <link rel="preload" href="{{ asset('assets/css/tailwind.output.css') }}" as="style">
    {{-- <link rel="preload" href="{{ asset('/css/app.css') }}" as="style"> --}}
    
    <!-- Preload skrip Alpine.js -->
    <link rel="preload" href="{{ asset('js/app.js') }}" as="script">
    <link rel="preload" href="{{ asset('windmill/init-alpine.js') }}" as="script">

    <!-- Prevent Flash of Unstyled Content with advanced technique -->
    <style id="fouc-blocker">
        /* Hide everything until fully loaded */
        html.loading * {
            -webkit-transition: none !important;
            -moz-transition: none !important;
            -ms-transition: none !important;
            -o-transition: none !important;
            transition: none !important;
        }
        
        html.loading body {
            visibility: hidden !important;
            opacity: 0 !important;
        }
        
        /* Critical dark mode styles */
        html.dark {
            background-color: #111827 !important;
        }
        
        html.dark body {
            background-color: #111827 !important;
            color: #f3f4f6 !important;
        }
    </style>
    
    <script>
        // Mark document as loading
        document.documentElement.classList.add('loading', 'dark');
        document.documentElement.style.backgroundColor = '#111827';
        document.documentElement.style.colorScheme = 'dark';
        
        // Counter for resources that need to load
        window.resourcesLoaded = 0;
        const RESOURCES_TO_WAIT = 2; // CSS and Alpine.js
        
        function checkAllResourcesLoaded() {
            window.resourcesLoaded++;
            if (window.resourcesLoaded >= RESOURCES_TO_WAIT) {
                // All resources loaded, remove loading class
                document.documentElement.classList.remove('loading');
            }
        }
        
        // Failsafe: If resources don't load, still show content after 1 second
        setTimeout(function() {
            document.documentElement.classList.remove('loading');
        }, 1000);
    </script>

    <!-- Load styles with onload handler -->
    <link href="{{ asset('assets/css/tailwind.output.css') }}" rel="stylesheet" 
          onload="checkAllResourcesLoaded()">
    @livewireStyles
    
    <style>
        /* Critical styles untuk mencegah flash */
        html.dark {
            background-color: #111827;
        }
        
        html.dark body {
            background-color: #111827;
            color: #f3f4f6;
        }
        
        /* Modal containment styles */
        main {
            position: relative; /* Makes main content area the positioning context */
        }
        
        .modal-container {
            position: absolute !important; /* Override any fixed positioning */
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            padding-right: 10vw;
            padding-left: 10vw;
            z-index: 25; /* Menurunkan dari 40 ke 25 agar di bawah sidebar (z-30) */
        }
        
        .modal-content {
            max-height: 80vh;
            margin: 2rem auto;
            overflow-y: auto;
        }
        
        /* Backdrop style for confirmation modal */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Fix untuk Alpine.js elements yang tersembunyi */
        [x-cloak] { 
            display: none !important; 
        }
    </style>
    
    <!-- Load Alpine.js with onload handler -->
    <script defer src="{{ asset('js/app.js') }}" onload="checkAllResourcesLoaded()"></script>
    <script src="{{ asset('windmill/init-alpine.js') }}"></script>
</head>
<body class="font-sans antialiased bg-gray-900">
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900" x-data="data()">
        <!-- Desktop sidebar -->
        <x-windmill.sidebar />
        
        <!-- Mobile sidebar -->
        <x-windmill.mobile-sidebar />
        
        <div class="flex flex-col flex-1">
            <x-windmill.header />
            
            <main class="h-full overflow-y-auto">
                <div class="container px-6 mx-auto grid">
                    @yield('header')
                    
                    @if(session()->has('message'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 dark:bg-green-900 dark:border-green-500 dark:text-green-200">
                            {{ session('message') }}
                        </div>
                    @endif
                    
                    @if(session()->has('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 dark:bg-red-900 dark:border-red-500 dark:text-red-200">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    <!-- Add noscript fallback for browsers without JS -->
    <noscript>
        <style>
            html.loading body {
                visibility: visible !important;
                opacity: 1 !important;
            }
        </style>
    </noscript>
    
    @livewireScripts
    @stack('scripts')
    
    <script>
        // Handle navigation events to prevent flash
        document.addEventListener('DOMContentLoaded', function() {
            // Enforce dark mode
            document.documentElement.classList.add('dark');
            
            // Store dark mode in storage for navigation
            function storeDarkMode() {
                localStorage.setItem('darkMode', 'enabled');
                
                // For navigation, set a special flag
                sessionStorage.setItem('navigatingFromDarkMode', 'true');
                sessionStorage.setItem('navigationTimestamp', Date.now().toString());
            }
            
            // Clean up modals
            function cleanupModals() {
                document.querySelectorAll('.modal-backdrop, .modal-container').forEach(el => {
                    el.remove();
                });
                
                // Reset Alpine.js state if needed
                if (typeof Alpine !== 'undefined') {
                    document.querySelectorAll('[x-data]').forEach(el => {
                        if (el.__x) {
                            if (typeof el.__x.$data.isSideMenuOpen !== 'undefined') {
                                el.__x.$data.isSideMenuOpen = false;
                            }
                            if (typeof el.__x.$data.isProfileMenuOpen !== 'undefined') {
                                el.__x.$data.isProfileMenuOpen = false;
                            }
                        }
                    });
                }
            }
            
            // Handle link clicks
            document.querySelectorAll('a:not([target="_blank"])').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Skip external links, anchors, etc.
                    if (
                        this.hostname !== window.location.hostname || 
                        this.getAttribute('href') === '#' ||
                        this.getAttribute('href') === 'javascript:void(0)' ||
                        e.ctrlKey || e.metaKey
                    ) {
                        return;
                    }
                    
                    storeDarkMode();
                    cleanupModals();
                });
            });
            
            // Handle form submissions
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    storeDarkMode();
                    cleanupModals();
                });
            });
            
            // Navigation events
            ['pageshow', 'popstate'].forEach(event => {
                window.addEventListener(event, function(e) {
                    document.documentElement.classList.add('dark');
                    cleanupModals();
                    
                    // Check if navigating from a dark mode page
                    if (sessionStorage.getItem('navigatingFromDarkMode') === 'true') {
                        // Check if navigation happened within the last 5 seconds
                        const timestamp = parseInt(sessionStorage.getItem('navigationTimestamp') || '0');
                        if (Date.now() - timestamp < 5000) {
                            document.documentElement.classList.add('dark');
                        }
                    }
                });
            });
            
            // For Livewire and Turbolinks
            ['livewire:load', 'turbolinks:load'].forEach(event => {
                document.addEventListener(event, function() {
                    document.documentElement.classList.add('dark');
                    cleanupModals();
                });
            });
            
            // Before page unload
            window.addEventListener('beforeunload', storeDarkMode);
        });
    </script>
</body>
</html>