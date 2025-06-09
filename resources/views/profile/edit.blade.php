@extends('layouts.windmill')

@section('title', 'Profile')

@section('header')
    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
        {{ __('Profile') }}
    </h2>
@endsection

@section('content')
    <div class="dark:bg-gray-900">
        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            @include('profile.partials.update-password-form')
        </div>

        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
@endsection
