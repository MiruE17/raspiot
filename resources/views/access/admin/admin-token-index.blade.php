<!-- filepath: resources/views/admin/users/livewire-index.blade.php -->
@extends('layouts.windmill')

@section('title', 'Manage API Tokens')

@section('header')
    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
        API Tokens
        <span class="text-sm font-normal block text-gray-600 dark:text-gray-400">Manage all user API tokens</span>
    </h2>
@endsection

@section('content')
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            @livewire('admin.token-manager')
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Trigger Alpine modals from Livewire
        document.addEventListener('livewire:load', function () {
            Livewire.on('open-modal', () => {
                window.dispatchEvent(new CustomEvent('open-modal'));
            });
            
            Livewire.on('token-created', () => {
                if (window.notyf) {
                    window.notyf.success('Token created successfully');
                }
            });
            
            Livewire.on('token-revoked', () => {
                if (window.notyf) {
                    window.notyf.success('Token revoked successfully');
                }
            });
        });
    </script>
@endpush