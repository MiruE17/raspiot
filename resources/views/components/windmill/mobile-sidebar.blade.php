<!-- filepath: c:\Users\Aji\Documents\raspiot\resources\views\components\windmill\mobile-sidebar.blade.php -->
@if(auth()->user()->is_admin)
    @include('components.windmill.admin-mobile-sidebar')
@else
    @include('components.windmill.user-mobile-sidebar')
@endif