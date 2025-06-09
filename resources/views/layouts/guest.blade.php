<!DOCTYPE html>
<html :class="{ 'dark': dark }" x-data="data()" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/init-alpine.js') }}"></script>
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="flex items-center min-h-screen p-6">
        <div class="flex-1 h-full max-w-md mx-auto overflow-hidden bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <div class="flex flex-col overflow-y-auto">
                @if (isset($logo))
                    <div class="flex items-center justify-center p-6">
                        {{ $logo }}
                    </div>
                @endif

                <div class="flex items-center justify-center p-6 sm:p-12 w-full">
                    <div class="w-full">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>