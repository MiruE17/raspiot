<div class="dark:bg-gray-900">
    <div x-data="{ 
            isModalOpen: false, 
            isDeleteModalOpen: $wire.entangle('showDeleteModal'),
            isNewTokenVisible: $wire.entangle('showNewToken') 
        }" 
        x-init="$watch('isDeleteModalOpen', value => console.log('Modal delete status:', value))"
        @keydown.escape.window="isModalOpen = false; isDeleteModalOpen = false">
        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <!-- Page header -->
            <div class="flex justify-between items-center mb-6">
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
                        placeholder="Search users or token ID..." 
                        aria-label="Search" 
                    />
                </div>

                <!-- Tombol Add Token -->
                <button
                    @click="$dispatch('open-modal')"
                    class="whitespace-nowrap inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add
                </button>
            </div>

            <!-- Display New Token -->
            @if($showNewToken && $newToken)
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg"
                 x-data="{
                     copyToken() {
                         const tokenInput = $refs.tokenInput;
                         if (!tokenInput) return;
                         tokenInput.select();
                         try {
                             if (document.execCommand('copy')) {
                                 if (window.notyf) {
                                     window.notyf.success('Token copied to clipboard!');
                                 } else {
                                     alert('Token copied to clipboard!');
                                 }
                             } else {
                                 throw new Error('execCommand did not succeed');
                             }
                         } catch (e) {
                             if (navigator.clipboard && window.isSecureContext) {
                                 navigator.clipboard.writeText(tokenInput.value)
                                     .then(() => {
                                         if (window.notyf) {
                                             window.notyf.success('Token copied to clipboard!');
                                         } else {
                                             alert('Token copied to clipboard!');
                                         }
                                     })
                                     .catch(err => {
                                         console.error('Clipboard API failed:', err);
                                         if (window.notyf) {
                                             window.notyf.error('Failed to copy token');
                                         } else {
                                             alert('Failed to copy token');
                                         }
                                     });
                             }
                         }
                     }
                 }">
                <div class="flex items-center mb-3">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">Token Created Successfully</h3>
                </div>
                
                <div class="mt-4 mb-4">
                    <div class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                        Your new token has been created. Copy it now as you won't be able to see it again:
                    </div>
                    <div class="flex space-x-2">
                        <div class="relative flex-1">
                            <input 
                                x-ref="tokenInput"
                                type="text" 
                                readonly 
                                value="{{ $newToken['full_token'] ?? '' }}" 
                                class="w-full pr-4 py-2 px-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm font-mono text-sm text-gray-800 dark:text-gray-200"
                            />
                        </div>
                        <button 
                            type="button"
                            @click="copyToken()"
                            class="ml-4 flex-shrink-0 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Copy
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-red-600 dark:text-red-400">
                        This token will only be displayed once. Store it in a secure place.
                    </p>
                </div>
                
                <div class="text-right">
                    <button 
                        wire:click="dismissNewToken"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    >
                        Dismiss
                    </button>
                </div>
            </div>
            @endif

            <!-- Token Table -->
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
                <div class="w-full overflow-x-auto">
                    <table class="w-full whitespace-no-wrap">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th wire:click="sortBy('id')" class="px-4 py-3 cursor-pointer">
                                    Token ID
                                    @if ($sortField === 'id')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th wire:click="sortBy('user_id')" class="px-4 py-3 cursor-pointer">
                                    Owner
                                    @if ($sortField === 'user_id')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th class="px-4 py-3">Token Preview</th>
                                <th wire:click="sortBy('hit_count')" class="px-4 py-3 cursor-pointer">
                                    Usage
                                    @if ($sortField === 'hit_count')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th wire:click="sortBy('last_hit')" class="px-4 py-3 cursor-pointer">
                                    Last Used
                                    @if ($sortField === 'last_hit')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th wire:click="sortBy('expiry_date')" class="px-4 py-3 cursor-pointer">
                                    Expires
                                    @if ($sortField === 'expiry_date')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th wire:click="sortBy('created_at')" class="px-4 py-3 cursor-pointer">
                                    Created
                                    @if ($sortField === 'created_at')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th wire:click="sortBy('active')" class="px-4 py-3 cursor-pointer">
                                    Status
                                    @if ($sortField === 'active')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($tokens as $token)
                                <tr class="text-gray-700 dark:text-gray-400">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center text-sm">
                                            <div>
                                                <p class="font-semibold">Token #{{ $token->id }}</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    @if($token->active)
                                                        Created {{ optional($token->created_at)->diffForHumans() ?? 'N/A' }}
                                                    @else
                                                        <span class="text-red-500 dark:text-red-400">Revoked {{ optional($token->updated_at)->diffForHumans() ?? 'N/A' }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 mr-3 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                                {{ substr($token->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold">{{ $token->user->name }}</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $token->user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="font-mono">{{ $token->getMaskedToken() }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ $token->hit_count }} hits
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ $token->getLastHitFormatted() }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ $token->getExpiryFormatted() }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ optional($token->created_at)->format('M d, Y') ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($token->active)
                                            @if($token->isExpired())
                                                <span class="px-2 py-1 font-semibold leading-tight text-orange-700 bg-orange-100 rounded-full dark:bg-orange-700 dark:text-orange-100">
                                                    Expired
                                                </span>
                                            @else
                                                <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                                    Active
                                                </span>
                                            @endif
                                        @else
                                            <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full dark:bg-red-700 dark:text-red-100">
                                                Revoked
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center space-x-2">
                                            @if($token->active)
                                                <button
                                                    wire:click="confirmDelete({{ $token->id }})"
                                                    class="p-1 text-red-600 hover:bg-red-100 dark:hover:bg-red-900 rounded-full dark:text-red-400 focus:outline-none focus:shadow-outline-red"
                                                    title="Revoke token"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                    </svg>
                                                </button>
                                            @else
                                                <span class="p-1 text-gray-400 cursor-not-allowed dark:text-gray-600" title="Token already revoked">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                        {{ $search ? 'No tokens found matching "' . $search . '"' : 'No tokens available. Generate your first token!' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="px-4 py-3 bg-white border-t dark:bg-gray-800 dark:border-gray-700">
                    {{ $tokens->links() }}
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div
            x-show="isDeleteModalOpen"
            x-cloak
            class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
            @click.self="isDeleteModalOpen = false"
        >
            <div
                x-show="isDeleteModalOpen"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 transform translate-y-1/2"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 transform translate-y-1/2"
                @click.away="isDeleteModalOpen = false"
                @keydown.escape="isDeleteModalOpen = false"
                class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl"
            >
                <!-- Modal header -->
                <header class="flex justify-between">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                            Revoke Token
                        </h2>
                    </div>
                    <!-- Close button -->
                    <button
                        class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover:text-gray-700"
                        aria-label="close"
                        @click="isDeleteModalOpen = false"
                    >
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </header>
                
                <!-- Modal body -->
                <div class="mt-4 mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Are you sure you want to revoke this token? Any applications using this token will no longer be able to access the API. The token will be marked as inactive but remain in your history.
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
                        Revoke
                    </button>
                </footer>
            </div>
        </div>
    </div>

    <!-- Modal section untuk create token -->
    <div 
        x-data="{ 
            open: false,
            init() {
                const that = this;
                window.addEventListener('open-modal', function() {
                    that.open = true;
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
                        Generate API Token
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
                <div class="mt-4 mb-6">
                    <!-- User Selection -->
                    <div class="mb-4">
                        <label for="userId" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Token Owner
                        </label>
                        <select 
                            id="userId" 
                            wire:model.defer="userId"
                            class="block w-full px-2 py-2 mt-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:ring focus:ring-blue-300 focus:ring-opacity-50 rounded-md shadow-sm dark:text-gray-300"
                        >
                            <option value="">Select a user</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('userId') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Expiry Date -->
                    <div class="mb-4">
                        <label for="expiryDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Expiry Date
                        </label>
                        <input 
                            type="date" 
                            id="expiryDate" 
                            wire:model.defer="expiryDate"
                            min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                            max="{{ date('Y-m-d', strtotime('+1 year')) }}"
                            class="block w-full px-2 py-2 mt-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:ring focus:ring-blue-300 focus:ring-opacity-50 rounded-md shadow-sm dark:text-gray-300"
                        >
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Blank expiry date will default to 1 day validity. Max Validity is 1 year
                        </p>
                        @error('expiryDate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <!-- Modal footer -->
                <footer class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">
                    <button
                        @click="open = false"
                        class="w-full px-5 py-3 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 sm:px-4 sm:py-2 sm:w-auto active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray"
                    >
                        Cancel
                    </button>
                    <button
                        class="w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
                        @click="open = false; $wire.createToken()"
                    >
                        Generate Token
                    </button>
                </footer>
            </div>
        </div>
    </div>

    <style>
        /* Hide x-cloak elements until Alpine.js is loaded */
        [x-cloak] { display: none !important; }
    </style>
</div>