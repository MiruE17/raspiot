<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="RaspIoT - Monitoring IoT Platform for Raspberry Pi">

        <title>RaspIoT - IoT Monitoring Platform</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <style>
            /* Reset styles */
            *, *::before, *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            
            html, body {
                height: 100%;
                overflow-x: hidden;
            }
            
            /* Base styles */
            :root {
                --bg-color: #111827;
                --text-color: #f3f4f6;
                --button-color: #3b82f6;
                --button-hover: #2563eb;
            }
            
            body {
                font-family: 'Figtree', sans-serif;
                background-color: var(--bg-color);
                color: var(--text-color);
                display: flex;
                flex-direction: column;
                height: 100%;
                width: 100%;
                position: relative;
            }
            
            .container {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 1rem;
                width: 100%;
            }
            
            .logo-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                max-width: 100%;
            }
            
            .logo {
                max-width: 240px;
                width: 100%;
                height: auto;
                margin-bottom: 1.5rem;
            }
            
            .subtitle {
                font-size: 1.1rem;
                color: #9ca3af;
                max-width: 600px;
                margin-bottom: 2rem;
                line-height: 1.5;
                text-align: center;
            }
            
            .login-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.75rem 2rem;
                background-color: var(--button-color);
                color: white;
                font-weight: 600;
                font-size: 1rem;
                border-radius: 9999px;
                transition: all 0.2s ease;
                text-decoration: none;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }
            
            .login-button:hover {
                background-color: var(--button-hover);
                transform: translateY(-1px);
            }
            
            .login-button:active {
                transform: translateY(0);
            }
            
            .footer {
                width: 100%;
                padding: 1rem;
                text-align: center;
                font-size: 0.875rem;
                color: #6b7280;
            }
            
            /* Dark mode dot pattern */
            .dark-pattern {
                background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
                background-attachment: fixed;
            }
            
            @media (max-width: 640px) {
                .subtitle {
                    font-size: 1rem;
                }
                
                .logo {
                    max-width: 200px;
                }
            }
        </style>
    </head>
    <body class="dark-pattern">
        <div class="container">
            <div class="logo-container">
                <img src="{{ asset('images/raspiot_logo_for_dark.png') }}" alt="RaspIoT Logo" class="logo">
                <p class="subtitle">RaspIoT - Raspberry Pi based IoT Monitoring and Data Dashboard</p>
                
                @if (Route::has('login'))
                    @auth
                        <a href="{{ auth()->user()->is_admin ? route('admin.home') : route('home') }}" 
                           class="login-button">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="login-button">
                            Login
                        </a>
                    @endauth
                @endif
            </div>
        </div>
        
        <footer class="footer">
            RaspIoT v1.0 (Laravel v{{ Illuminate\Foundation\Application::VERSION }} | PHP v{{ PHP_VERSION }})
        </footer>
    </body>
</html>
