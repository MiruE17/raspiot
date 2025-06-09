<!-- filepath: c:\Users\Aji\Documents\raspiot\resources\views\user\tokens.blade.php -->
@extends('layouts.windmill')

@section('title', 'API Tokens')

@section('header')
    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
        API Tokens
        <span class="text-sm font-normal block text-gray-600 dark:text-gray-400">Manage your API access tokens</span>
    </h2>
@endsection

@section('content')
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            @livewire('user.token-manager')
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('livewire:load', function () {
            // Handle modal events
            Livewire.on('show-modal', () => {
                window.dispatchEvent(new CustomEvent('open-modal'));
            });
            
            // Verbesserte Copy-to-Clipboard Funktion
            Livewire.on('copy-to-clipboard', function(params) {
                console.log('Copy event received with exact params:', JSON.stringify(params));
                
                // Validasi parameter dengan lebih detil
                if (!params) {
                    console.error('Params is completely undefined');
                    return;
                }
                
                if (typeof params !== 'object') {
                    console.error('Params is not an object but a:', typeof params);
                    return;
                }
                
                if (!('text' in params)) {
                    console.error('No text property in params. Keys:', Object.keys(params));
                    return;
                }
                
                const text = params.text.trim();
                console.log('Text length to copy:', text.length);
                
                if (!text || text.length === 0) {
                    console.error('Empty text to copy');
                    showFeedback(false, 'Nothing to copy - token is empty');
                    return;
                }
                
                // Metode 1: execCommand dengan select input
                const tokenInput = document.getElementById('token-display');
                if (tokenInput) {
                    try {
                        console.log('Trying copy via input selection');
                        tokenInput.value = text; // Pastikan nilai terbaru
                        tokenInput.focus();
                        tokenInput.select();
                        
                        if (document.execCommand('copy')) {
                            console.log('Copy success via input selection');
                            showFeedback(true, 'Token copied to clipboard!');
                            return;
                        }
                    } catch (e) {
                        console.warn('Input selection method failed:', e);
                    }
                }
                
                // Metode 2: Clipboard API (modern browsers)
                if (navigator.clipboard && window.isSecureContext) {
                    console.log('Trying Clipboard API');
                    navigator.clipboard.writeText(text)
                        .then(() => {
                            console.log('Copy success via Clipboard API');
                            showFeedback(true, 'Token copied to clipboard!');
                        })
                        .catch(err => {
                            console.warn('Clipboard API failed:', err);
                            // Try fallback for incognito
                            copyViaTextarea(text);
                        });
                } else {
                    // Metode 3: Textarea Fallback (works in more browsers)
                    console.log('Clipboard API not available, using textarea fallback');
                    copyViaTextarea(text);
                }
            });
            
            // Tambahkan listener untuk custom "copy-token-event" yang di-dispatch oleh Alpine
            document.addEventListener('copy-token-event', event => {
                console.log('copy-token-event captured with detail:', event.detail);
                // Teruskan event ke Livewire
                Livewire.emit('copy-token-event', event.detail);
            });
            
            // Event handlers for other token operations
            Livewire.on('token-created', () => showFeedback(true, 'Token created successfully'));
            Livewire.on('token-revoked', () => showFeedback(true, 'Token revoked successfully'));
            Livewire.on('token-copy-error', () => showFeedback(false, 'Failed to copy: Token not available'));
        });
        
        // Textarea fallback method
        function copyViaTextarea(text) {
            console.log('Using textarea fallback method');
            try {
                // Create a temporary textarea
                const textarea = document.createElement('textarea');
                textarea.value = text;
                
                // Make it work in both visible and invisible modes
                if (navigator.userAgent.indexOf('Firefox') !== -1 || 
                    navigator.userAgent.indexOf('Edg') !== -1 || 
                    navigator.userAgent.indexOf('Chrome') !== -1) {
                    // These browsers need the element to be visible and have focus
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '1';
                    textarea.style.top = '10px';
                    textarea.style.left = '10px';
                    textarea.style.width = '200px';
                    textarea.style.height = '50px';
                    textarea.style.zIndex = '9999';
                } else {
                    // Hide for other browsers
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    textarea.style.pointerEvents = 'none';
                    textarea.style.top = '10px';
                    textarea.style.left = '10px';
                }
                
                // Add to document, select text
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                
                // Try to copy
                const successful = document.execCommand('copy');
                
                // Special handling for Firefox in private mode
                if (navigator.userAgent.indexOf('Firefox') !== -1) {
                    // Keep visible for a moment to ensure copy works
                    setTimeout(() => {
                        document.body.removeChild(textarea);
                    }, 500);
                } else {
                    document.body.removeChild(textarea);
                }
                
                // Show feedback
                if (successful) {
                    console.log('Textarea fallback copy succeeded');
                    showFeedback(true, 'Token copied to clipboard!');
                } else {
                    console.error('Textarea fallback failed with execCommand');
                    showFeedback(false, 'Could not copy automatically. Please select and copy the token manually.');
                    // Try to make the main token input more accessible for manual copy
                    const tokenInput = document.getElementById('token-display');
                    if (tokenInput) {
                        tokenInput.focus();
                        tokenInput.select();
                        showManualCopyHelp();
                    }
                }
            } catch (err) {
                console.error('Textarea fallback complete error:', err);
                showFeedback(false, 'Could not copy token to clipboard. Please copy it manually.');
                showManualCopyHelp();
            }
        }
        
        // Helper for showing feedback
        function showFeedback(success, message) {
            if (window.notyf) {
                if (success) {
                    window.notyf.success(message);
                } else {
                    window.notyf.error(message);
                }
            } else {
                alert(message);
            }
        }
        
        // Show help for manual copy
        function showManualCopyHelp() {
            // Add visible instructions for manual copy if needed
            const tokenContainer = document.getElementById('token-display').closest('.flex');
            if (tokenContainer) {
                const helpText = document.createElement('div');
                helpText.className = 'text-sm text-yellow-600 dark:text-yellow-400 mt-2';
                helpText.textContent = 'Please press Ctrl+C (or Cmd+C on Mac) to copy the token manually.';
                
                // Only add if not already present
                if (!tokenContainer.querySelector('.text-yellow-600')) {
                    tokenContainer.appendChild(helpText);
                }
            }
        }
    </script>
    
    <style>
        /* Hide the x-cloak elements until Alpine.js is loaded */
        [x-cloak] { display: none !important; }
    </style>
@endpush