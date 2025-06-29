<!-- filepath: resources/views/livewire/user-manager.blade.php -->
<div class="dark:bg-gray-900">
    <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <!-- Search Box -->
        <div class="flex justify-between items-center mb-4">
            <div class="relative w-full max-w-md mr-6">
                <div class="absolute inset-y-0 flex items-center pl-3 ml-3">
                    <svg class="w-5 ml-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    class="w-full pl-10 pr-4 py-2 text-sm text-gray-700 bg-gray-100 border-0 rounded-md dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue" 
                    type="text" 
                    placeholder="Search users..." 
                    aria-label="Search" 
                />
            </div>
            
            <button wire:click="create" class="whitespace-nowrap inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add
            </button>
        </div>
        
        <!-- User Table -->
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th wire:click="sortBy('name')" class="px-4 py-3 cursor-pointer">
                                Name
                                @if ($sortField === 'name')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th wire:click="sortBy('email')" class="px-4 py-3 cursor-pointer">
                                Email
                                @if ($sortField === 'email')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th wire:click="sortBy('is_admin')" class="px-4 py-3 cursor-pointer">
                                Role
                                @if ($sortField === 'is_admin')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th wire:click="sortBy('created_at')" class="px-4 py-3 cursor-pointer">
                                Created
                                @if ($sortField === 'created_at')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @forelse ($users as $user)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div class="mr-3 flex-shrink-0">
                                            <img
                                                class="object-cover w-8 h-8 rounded-full"
                                                src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF"
                                                alt="{{ $user->name }}"
                                                aria-hidden="true"
                                            />
                                        </div>
                                        <div>
                                            <p class="font-semibold">{{ $user->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $user->email }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($user->is_admin)
                                        <span class="px-2 py-1 font-semibold leading-tight text-gray-700 bg-green-100 rounded-full dark:bg-green-500 dark:text-purple-100">
                                            Admin
                                        </span>
                                    @else
                                        <span class="px-2 py-1 font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-100">
                                            User
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            wire:click="view({{ $user->id }})"
                                            class="p-1 text-blue-600 rounded-full dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900 focus:outline-none focus:shadow-outline-blue"
                                            aria-label="View"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="edit({{ $user->id }})"
                                            class="p-1 text-green-600 rounded-full dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900 focus:outline-none focus:shadow-outline-green"
                                            aria-label="Edit"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="confirmDelete({{ $user->id }})"
                                            class="p-1 text-red-600 rounded-full dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900 focus:outline-none focus:shadow-outline-red"
                                            aria-label="Delete"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    {{ $search ? 'No users found matching "' . $search . '"' : 'No users available' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 bg-white border-t dark:bg-gray-800 dark:border-gray-700">
                {{ $users->links() }}
            </div>
        </div>
    </div>
    
    <!-- Modal untuk form user - KONSISTEN DENGAN CONTROLLER -->
    <div 
        x-data="{ 
            open: false,
            init() {
                const that = this;
                window.addEventListener('show-modal', function() {
                    that.open = true;
                });
                window.addEventListener('hide-modal', function() {
                    that.open = false;
                });
            }
        }"
        x-cloak
    >
        <!-- Modal backdrop -->
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
            @click.self="open = false"
        >
            <!-- Modal -->
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 transform translate-y-1/2"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 transform translate-y-1/2"
                @click.away="open = false"
                @keydown.escape="open = false"
                class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl"
            >
                <!-- Modal header -->
                <header class="flex justify-between">
                    <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        {{ $viewMode ? 'User Details' : ($userId ? 'Edit User' : 'Create User') }}
                    </h2>
                    <!-- Close button -->
                    <button
                        class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover:text-gray-700"
                        aria-label="close"
                        @click="open = false"
                    >
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </header>
                
                <!-- Modal body -->
                <div class="mt-4 mb-6 overflow-y-auto" style="max-height: 60vh;">
                    @if($viewMode && $selectedUser)
                        <!-- View mode content - tidak perlu diubah -->
                        <div>
                            <div class="mb-4 flex items-center">
                                <img
                                    class="object-cover w-16 h-16 rounded-full mr-4"
                                    src="https://ui-avatars.com/api/?name={{ urlencode($selectedUser->name) }}&size=128&color=7F9CF5&background=EBF4FF"
                                    alt="{{ $selectedUser->name }}"
                                    aria-hidden="true"
                                />
                                <div>
                                    <span class="text-lg font-medium text-gray-700 dark:text-gray-300">{{ $selectedUser->name }}</span>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedUser->email }}</p>
                                </div>
                            </div>
                            
                            <!-- Konten lainnya tetap sama -->
                        </div>
                    @else
                        <!-- Edit/Create mode content - tidak perlu diubah -->
                        <form id="userForm" wire:submit.prevent="store">
                            <div class="grid gap-6 mb-6 md:grid-cols-2">
                                <!-- Name field -->
                                <div class="col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                                    <input wire:model="name" type="text" id="name" class="px-2 py-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-gray-500" required>
                                    @error('name')
                                        <span class="text-xs text-red-600 dark:text-red-400">{{ $errors->first('name') }}</span>
                                    @enderror
                                </div>

                                <!-- Email field -->
                                <div class="col-span-2">
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                                    <input wire:model="email" type="email" id="email" class="px-2 py-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-gray-500" required>
                                    @error('email')
                                        <span class="text-xs text-red-600 dark:text-red-400">{{ $errors->first('email') }}</span>
                                    @enderror
                                </div>

                                <!-- Role selection -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Role</label>
                                    <div class="mt-2 space-y-2">
                                        <div class="flex items-center">
                                            <input wire:model="is_admin" type="radio" id="role_user" name="role" value="0" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                            <label for="role_user" class="ml-2 block text-sm text-gray-700 dark:text-gray-400">User</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="is_admin" type="radio" id="role_admin" name="role" value="1" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                            <label for="role_admin" class="ml-2 block text-sm text-gray-700 dark:text-gray-400">Admin</label>
                                        </div>
                                    </div>
                                    @error('is_admin')
                                        <span class="text-xs text-red-600 dark:text-red-400">{{ $errors->first('is_admin') }}</span>
                                    @enderror
                                </div>

                                <!-- Change Password (only for edit) -->
                                @if($userId)
                                <div class="col-span-2">
                                    <div class="flex items-center">
                                        <!-- Tambahkan .live agar perubahan langsung diterapkan -->
                                        <input wire:model.live="changePassword" type="checkbox" id="changePassword" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700">
                                        <label for="changePassword" class="ml-2 block text-sm text-gray-700 dark:text-gray-400">Change Password</label>
                                    </div>
                                </div>
                                @endif

                                <!-- Password fields -->
                                @if(!$userId || $changePassword)
                                <div class="col-span-2">
                                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Password</label>
                                    <input wire:model="password" type="password" id="password" class="px-2 py-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-gray-500" required>
                                    @error('password')
                                        <span class="text-xs text-red-600 dark:text-red-400">{{ $errors->first('password') }}</span>
                                    @enderror
                                </div>

                                <div class="col-span-2">
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Confirm Password</label>
                                    <input wire:model="password_confirmation" type="password" id="password_confirmation" class="px-2 py-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-gray-500" required>
                                </div>
                                @endif
                            </div>
                        </form>
                    @endif
                </div>
                
                <!-- Modal footer -->
                <footer class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">
                    @if($viewMode && $selectedUser)
                        <button
                            @click="open = false"
                            class="w-full px-5 py-3 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 sm:px-4 sm:py-2 sm:w-auto active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray"
                        >
                            Close
                        </button>
                    @else
                        <button
                            @click="open = false"
                            class="w-full px-5 py-3 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 sm:px-4 sm:py-2 sm:w-auto active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray"
                        >
                            Cancel
                        </button>
                        <button
                            wire:click="store" 
                            type="button"
                            class="w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
                        >
                            {{ $userId ? 'Update' : 'Create' }}
                        </button>
                    @endif
                </footer>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal - MENGIKUTI TOKEN MANAGER -->
    <div
        x-data="{ show: @entangle('showDeleteModal') }"
        x-show="show"
    >
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
            @click.self="show = false"
        >
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 transform translate-y-1/2"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 transform translate-y-1/2"
                @click.away="show = false"
                @keydown.escape="show = false"
                class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl"
            >
                <!-- Modal header -->
                <header class="flex justify-between">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                            Delete User
                        </h2>
                    </div>
                    <!-- Close button -->
                    <button
                        class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover:text-gray-700"
                        aria-label="close"
                        @click="show = false"
                    >
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </header>
                
                <!-- Modal body -->
                <div class="mt-4 mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        This user will be deleted. This action cannot be undone.
                    </p>
                </div>
                
                <!-- Modal footer -->
                <footer class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">
                    <button
                        wire:click="cancelDelete"
                        type="button"
                        class="w-full px-5 py-3 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 sm:px-4 sm:py-2 sm:w-auto active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="delete"
                        type="button"
                        class="w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red"
                    >
                        Delete
                    </button>
                </footer>
            </div>
        </div>
    </div>

    <!-- Style untuk x-cloak -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
