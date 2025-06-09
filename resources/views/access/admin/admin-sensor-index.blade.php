<!-- filepath: resources/views/admin/users/livewire-index.blade.php -->
@extends('layouts.windmill')

@section('title', 'Manage Sensors')

@section('header')
    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
        Sensors
        <span class="text-sm font-normal block text-gray-600 dark:text-gray-400">Manage system sensors</span>
    </h2>
@endsection

@section('content')
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            @livewire('admin.sensor-manager')
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
            
            Livewire.on('sensor-created', () => {
                if (window.notyf) {
                    window.notyf.success('Sensor created successfully');
                }
            });
            
            Livewire.on('sensor-updated', () => {
                if (window.notyf) {
                    window.notyf.success('Sensor updated successfully');
                }
            });
            
            Livewire.on('sensor-deleted', () => {
                if (window.notyf) {
                    window.notyf.success('Sensor deleted successfully');
                }
            });
        });
    </script>
@endpush