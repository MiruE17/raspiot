@extends('layouts.windmill')

@section('title', isset($schemeId) ? 'Scheme Details' : 'My Schemes')

@section('header')
    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
        @if(!isset($schemeId))
            My Schemes
            <span class="text-sm font-normal block text-gray-600 dark:text-gray-400">Manage your IoT data collection schemes</span>
        @endif
    </h2>
@endsection

@section('content')
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            @if(isset($schemeId))
                @livewire('user.scheme-dashboard', ['schemeId' => $schemeId])
            @else
                @livewire('user.scheme-manager')
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        /* Hide the x-cloak elements until Alpine.js is loaded */
        [x-cloak] { display: none !important; }
    </style>
@endpush