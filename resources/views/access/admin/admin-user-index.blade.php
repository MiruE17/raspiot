<!-- filepath: resources/views/admin/users/livewire-index.blade.php -->
@extends('layouts.windmill')

@section('title', 'Manage Users')

@section('header')
    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
        Users
        {{-- <span class="text-sm font-normal block text-gray-600 dark:text-gray-400">Manage system users</span> --}}
    </h2>
@endsection

@section('content')
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            @livewire('admin.user-manager')
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Trigger Alpine modals from Livewire
        document.addEventListener('livewire:initialized', function () {
            // Mengubah showModal menjadi show-modal agar konsisten dengan controller
            Livewire.on('show-modal', () => {
                console.log('Show modal event received');
                window.dispatchEvent(new CustomEvent('show-modal'));
            });
            
            Livewire.on('hide-modal', () => {
                console.log('Hide modal event received');
                window.dispatchEvent(new CustomEvent('hide-modal'));
            });
            
            Livewire.on('userSaved', () => {
                // Do something after user is saved
            });
        });
    </script>
@endpush