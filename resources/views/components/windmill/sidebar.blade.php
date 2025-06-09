<!-- filepath: c:\Users\Aji\Documents\raspiot\resources\views\components\windmill\sidebar.blade.php -->
@if(auth()->user()->is_admin)
    @include('components.windmill.admin-sidebar')
@else
    @include('components.windmill.user-sidebar')
@endif