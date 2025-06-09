<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Forgot Password - RaspIoT</title>

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
                --input-bg: #1f2937;
                --input-border: #374151;
                --input-focus: #3b82f6;
                --error-color: #ef4444;
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
                margin-bottom: 2rem;
            }
            
            .logo {
                max-width: 180px;
                width: 100%;
                height: auto;
                margin-bottom: 1rem;
            }
            
            .form-container {
                background-color: rgba(31, 41, 55, 0.5);
                border-radius: 0.75rem;
                padding: 2rem;
                width: 100%;
                max-width: 28rem;
                border: 1px solid rgba(75, 85, 99, 0.2);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
            
            .description {
                margin-bottom: 1.5rem;
                font-size: 0.875rem;
                color: #d1d5db;
                line-height: 1.5;
            }
            
            .input-label {
                display: block;
                font-size: 0.875rem;
                font-weight: 500;
                margin-bottom: 0.5rem;
                color: #e5e7eb;
            }
            
            .text-input {
                width: 100%;
                padding: 0.5rem 0.75rem;
                background-color: var(--input-bg);
                border: 1px solid var(--input-border);
                border-radius: 0.5rem;
                color: #f3f4f6;
                font-size: 0.875rem;
                transition: border-color 0.15s ease-in-out;
            }
            
            .text-input:focus {
                outline: none;
                border-color: var(--input-focus);
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
            }
            
            .form-footer {
                display: flex;
                justify-content: flex-end;
                align-items: center;
                margin-top: 1.5rem;
            }
            
            .back-link {
                font-size: 0.875rem;
                color: #9ca3af;
                text-decoration: none;
                margin-right: auto;
                transition: color 0.15s ease-in-out;
            }
            
            .back-link:hover {
                color: #e5e7eb;
                text-decoration: underline;
            }
            
            .submit-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.5rem 1.5rem;
                background-color: var(--button-color);
                color: white;
                font-weight: 600;
                font-size: 0.875rem;
                border: none;
                border-radius: 0.25rem; /* Diubah dari 9999px ke 0.75rem agar sesuai dengan form-container */
                transition: all 0.2s ease;
                cursor: pointer;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }
            
            .submit-button:hover {
                background-color: var(--button-hover);
                transform: translateY(-1px);
            }
            
            .submit-button:active {
                transform: translateY(0);
            }
            
            .error-message {
                color: var(--error-color);
                font-size: 0.75rem;
                margin-top: 0.25rem;
            }
            
            .status-message {
                margin-bottom: 1rem;
                padding: 0.75rem;
                border-radius: 0.5rem;
                background-color: rgba(59, 130, 246, 0.1);
                border: 1px solid rgba(59, 130, 246, 0.2);
                color: #93c5fd;
            }
            
            .mb-2 {
                margin-bottom: 0.5rem;
            }
            
            .mt-4 {
                margin-top: 1rem;
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
                .form-container {
                    padding: 1.5rem;
                }
                
                .logo {
                    max-width: 150px;
                }
            }
        </style>
    </head>
    <body class="dark-pattern">
        <div class="container">
            <div class="logo-container">
                <img src="{{ asset('images/raspiot_logo_for_dark.png') }}" alt="RaspIoT Logo" class="logo">
            </div>
            
            <div class="form-container">
                <div class="description">
                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                </div>
                
                @if (session('status'))
                    <div class="status-message">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-2">
                        <label for="email" class="input-label">Email</label>
                        <input id="email" class="text-input" type="email" name="email" value="{{ old('email') }}" required autofocus />
                        @error('email')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('login') }}" class="back-link">
                            Back to login
                        </a>
                        
                        <button type="submit" class="submit-button">
                            {{ __('Email Password Reset Link') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <footer class="footer">
            RaspIoT v1.0 (Laravel v{{ Illuminate\Foundation\Application::VERSION }} | PHP v{{ PHP_VERSION }})
        </footer>
    </body>
</html>
