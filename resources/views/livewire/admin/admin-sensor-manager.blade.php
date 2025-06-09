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
                    placeholder="Search sensors..." 
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
        
        <!-- Sensor Table - Menambahkan kolom gambar -->
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <div class="flex justify-between mt-2 mb-4 ml-2">
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400 mr-2">Show</span>
                        <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">entries</span>
                    </div>
                </div>
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">Image</th>
                            <th wire:click="sortBy('name')" class="px-4 py-3 cursor-pointer">
                                Name
                                @if ($sortField === 'name')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th class="px-4 py-3">Description</th>
                            <th wire:click="sortBy('num_of_outputs')" class="px-4 py-3 cursor-pointer">
                                Outputs
                                @if ($sortField === 'num_of_outputs')
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
                        @forelse ($sensors as $sensor)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        @if($sensor->picture)
                                            <!-- Div pembungkus dengan ukuran tetap -->
                                            <div class="flex items-center justify-center rounded border border-gray-300 dark:border-gray-600" style="width: 7.25rem; height: 7.25rem;">
                                                <img src="{{ Storage::url($sensor->picture) }}" alt="{{ $sensor->name }}" 
                                                     class="p-2 object-contain max-w-full max-h-full">
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center rounded bg-gray-200 dark:bg-gray-700" style="width:7.25rem; height:7.25rem;">
                                                <!-- Icon kamera dengan garis coret langsung di dalam SVG -->
                                                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <!-- Path untuk icon kamera -->
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    
                                                    <!-- Garis diagonal coret -->
                                                    <line x1="3" y1="3" x2="21" y2="21" stroke="red" stroke-width="2" stroke-linecap="round" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div>
                                            <p class="font-semibold">{{ $sensor->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ Str::limit($sensor->description, 50) ?: 'No description' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $sensor->num_of_outputs }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $sensor->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            wire:click="view({{ $sensor->id }})"
                                            class="p-1 text-blue-600 rounded-full dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900 focus:outline-none focus:shadow-outline-blue" 
                                            aria-label="View"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="edit({{ $sensor->id }})"
                                            class="p-1 text-green-600 rounded-full dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900 focus:outline-none focus:shadow-outline-green"
                                            aria-label="Edit"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </button>
                                        <button
                                            wire:click="confirmDelete({{ $sensor->id }})"
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
                                    {{ $search ? 'No sensors found matching "' . $search . '"' : 'No sensors available' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 bg-white border-t dark:bg-gray-800 dark:border-gray-700">
                {{ $sensors->links() }}
            </div>
        </div>
    </div>
    
    <!-- Modal dialog untuk form sensor - STRUKTUR BARU YANG KONSISTEN DENGAN CONTROLLER -->
    <div x-data="{ 
            show: false,
            init() {
                const that = this;
                window.addEventListener('show-modal', function() {
                    that.show = true;
                });
                window.addEventListener('hide-modal', function() {
                    that.show = false;
                });
            }
        }"
        x-cloak>

        <!-- Modal backdrop - STRUKTUR DISEDERHANAKAN -->
        <div x-show="show" 
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
             @click.self="show = false">
        
            <!-- Modal content - STRUKTUR DISEDERHANAKAN -->
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 transform translate-y-1/2"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0 transform translate-y-1/2"
                 @click.away="show = false"
                 @keydown.escape="show = false"
                 class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl">
                 
                <!-- Header -->
                <header class="flex justify-between">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        {{ $viewMode ? 'Sensor Details' : ($sensorId ? 'Edit Sensor' : 'Create Sensor') }}
                    </h3>
                    <button @click="show = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </header>
                
                <!-- Modal Content with Scrolling -->
                <div class="mt-4 mb-6 overflow-y-auto" style="max-height: 60vh;">
                    <!-- Konten tetap sama -->
                    @if($viewMode && $selectedSensor)
                        <!-- View mode content -->
                        @if($selectedSensor->picture)
                            <div class="mb-4">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Image</span>
                                <div class="mt-2">
                                    <img src="{{ Storage::url($selectedSensor->picture) }}" alt="Sensor Image" 
                                         class="h-48 w-auto object-contain rounded border border-gray-300 dark:border-gray-600">
                                </div>
                            </div>
                        @endif
                        
                        <div class="mb-4">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Name</span>
                            <p class="text-gray-700 dark:text-gray-300">{{ $selectedSensor->name }}</p>
                        </div>
                        
                        <div class="mb-4">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Description</span>
                            <p class="text-gray-700 dark:text-gray-300">{{ $selectedSensor->description ?? 'No description' }}</p>
                        </div>
                        
                        <div class="mb-4">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Number of Outputs</span>
                            <p class="text-gray-700 dark:text-gray-300">{{ $selectedSensor->num_of_outputs }}</p>
                        </div>
                        
                        <div class="mb-4">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Output Labels</span>
                            <div class="mt-2 space-y-2">
                                @foreach(explode(',', $selectedSensor->output_labels) as $index => $label)
                                    <div class="flex items-center">
                                        <span class="mr-2 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $index + 1 }}.</span>
                                        <span class="text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Created At</span>
                            <p class="text-gray-700 dark:text-gray-300">{{ $selectedSensor->created_at->format('F j, Y g:i A') }}</p>
                        </div>
                    @else
                        <!-- Edit/Create mode content -->
                        <form id="sensorForm" wire:submit.prevent="store">
                            <div>
                                <!-- Picture Upload - dipindahkan ke posisi pertama -->
                                <div class="block text-sm text-gray-700 dark:text-gray-400">
                                    <span>Sensor Picture</span>
                                    
                                    @if($existingPicture)
                                        <div class="mt-2 mb-2">
                                            <img src="{{ Storage::url($existingPicture) }}" alt="Sensor Image" 
                                                 class="h-32 w-auto object-contain rounded border border-gray-300 dark:border-gray-600">
                                            
                                            <button type="button" wire:click="removePicture" 
                                                   class="mt-2 px-3 py-1 text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                Remove Image
                                            </button>
                                        </div>
                                    @endif
                                    
                                    <div class="relative">
                                        <input type="file" wire:model="picture" id="picture" 
                                               class="mt-1 text-sm text-gray-600 dark:text-gray-400
                                                      file:mr-4 file:py-2 file:px-4
                                                      file:rounded file:border-0
                                                      file:text-sm file:font-semibold
                                                      file:bg-blue-50 file:text-blue-700
                                                      hover:file:bg-blue-100
                                                      dark:file:bg-gray-700 dark:file:text-gray-300" />
                                        
                                        <div wire:loading wire:target="picture" class="absolute inset-0 bg-white bg-opacity-75 dark:bg-gray-800 dark:bg-opacity-75 flex items-center justify-center">
                                            <span class="text-xs text-blue-600 dark:text-blue-400 flex items-center">
                                                <svg class="w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Uploading...
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @error('picture')
                                        <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                                    @enderror
                                    
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Upload a PNG, JPG, or GIF image (max 1MB).
                                    </div>
                                </div>
                                
                                <!-- Name -->
                                <label class="block mt-4 text-sm text-gray-700 dark:text-gray-400">
                                    <span>Name</span>
                                    <input wire:model.defer="name" class="block w-full mt-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Sensor name" />
                                    @error('name') <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                                </label>
                                
                                <!-- Description -->
                                <label class="block mt-4 text-sm text-gray-700 dark:text-gray-400">
                                    <span>Description</span>
                                    <textarea wire:model.defer="description" class="block w-full mt-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-textarea" rows="3" placeholder="Sensor description"></textarea>
                                    @error('description') <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                                </label>
                                
                                <!-- Number of outputs -->
                                <label class="block mt-4 text-sm text-gray-700 dark:text-gray-400">
                                    <span>Number of Outputs</span>
                                    <div class="flex items-center mt-1">
                                        <!-- Wrapper div with width matching content -->
                                        <div style="width: auto; max-width: 40%;">
                                            <!-- Container -->
                                            <div class="flex items-center border dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
                                                <!-- Decrement element -->
                                                <div 
                                                    wire:click="$set('num_of_outputs', Math.max(1, parseInt($wire.get('num_of_outputs')) - 1))"
                                                    @click.stop
                                                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border-r border-gray-300 dark:border-gray-600 focus:outline-none cursor-pointer"
                                                >
                                                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                    </svg>
                                                </div>
                                                
                                                <!-- Input field -->
                                                <input 
                                                    style="width:80px"
                                                    wire:model.live="num_of_outputs" 
                                                    type="number" 
                                                    min="1"
                                                    class="py-2 block text-center border-0 focus:ring-0 text-sm dark:bg-gray-700 dark:text-gray-300 focus:outline-none" 
                                                    placeholder="1" 
                                                />
                                                
                                                <!-- Increment element -->
                                                <div 
                                                    wire:click="$set('num_of_outputs', parseInt($wire.get('num_of_outputs')) + 1 || 1)"
                                                    @click.stop
                                                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border-l border-gray-300 dark:border-gray-600 focus:outline-none cursor-pointer"
                                                >
                                                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @error('num_of_outputs') <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                                </label>
                                
                                <!-- Output labels -->
                                <div class="block mt-4 text-sm text-gray-700 dark:text-gray-400">
                                    <span class="font-medium">Output Labels & Validation</span>
                                    <div class="mt-2 space-y-4">
                                        @foreach($output_labels as $index => $label)
                                            <div class="flex flex-wrap items-center" style="gap: 1%;">
                                                <!-- Label number (10%) -->
                                                <div style="width: 7.5%; height: 100%;">
                                                    <div class="block items-center justify-center w-10" style="height: 100%">
                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">#</label>
                                                        <span
                                                        style="text-align: center;"
                                                            class="block w-full text-sm bg-gray-100 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed form-input"
                                                        />{{ $index + 1 }}</span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Label input (40%) -->
                                                <div style="width: 25%;">
                                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Label</label>
                                                    <input 
                                                        wire:model.defer="output_labels.{{ $index }}" 
                                                        class="block w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" 
                                                        placeholder="val_{{ $index + 1 }}" 
                                                    />
                                                </div>
                                                
                                                <!-- Data Type Selection (default to number) -->
                                                <div style="width: 25%;">
                                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Data Type</label>
                                                    <select 
                                                        wire:model.live="output_data_types.{{ $index }}"
                                                        class="block w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-select"
                                                    >
                                                        <option value="number">Number</option>
                                                        <option value="string">Text</option>
                                                        <option value="boolean">Boolean</option>
                                                        <option value="percentage">Percentage</option>
                                                    </select>
                                                </div>
                                                
                                                <!-- Type-specific validation fields -->
                                                @if($output_data_types[$index] === 'number')
                                                    <!-- Min Value (15%) -->
                                                    <div style="width: 17.5%;">
                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Min.</label>
                                                        <input 
                                                            wire:model.defer="output_min_values.{{ $index }}" 
                                                            type="number" 
                                                            class="block w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" 
                                                        />
                                                    </div>
                                                    
                                                    <!-- Max Value (15%) -->
                                                    <div style="width: 17.5%;">
                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Max.</label>
                                                        <input 
                                                            wire:model.defer="output_max_values.{{ $index }}" 
                                                            type="number" 
                                                            class="block w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" 
                                                        />
                                                    </div>
                                                @endif
                                                
                                                @if($output_data_types[$index] === 'percentage')
                                                    <!-- Min Value (15%) -->
                                                    <div style="width: 17.5%;">
                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Min. (decimal)</label>
                                                        <input 
                                                            wire:model.defer="output_min_values.{{ $index }}" 
                                                            type="number" 
                                                            step="0.01"
                                                            class="block w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" 
                                                        />
                                                    </div>
                                                    
                                                    <!-- Max Value (15%) -->
                                                    <div style="width: 17.5%;">
                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Max. (decimal)</label>
                                                        <input 
                                                            wire:model.defer="output_max_values.{{ $index }}" 
                                                            type="number" 
                                                            step="0.01"
                                                            class="block w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-400 focus:outline-none focus:shadow-outline-blue dark:text-gray-300 dark:focus:shadow-outline-gray form-input" 
                                                        />
                                                    </div>
                                                @endif
                                                
                                                <!-- String type field (30%) -->
                                                @if($output_data_types[$index] === 'string')
                                                    <div style="width: 35%;">
                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Max Length</label>
                                                        <input 
                                                            type="text" 
                                                            value="16 characters" 
                                                            disabled
                                                            class="block w-full text-sm bg-gray-100 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed form-input" 
                                                        />
                                                    </div>
                                                @endif
                                                
                                                <!-- Boolean type field (30%) -->
                                                @if($output_data_types[$index] === 'boolean')
                                                    <div style="width: 35%;">
                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Format</label>
                                                        <input 
                                                            type="text" 
                                                            value="0/1" 
                                                            disabled
                                                            readonly
                                                            class="block w-full text-sm bg-gray-100 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed form-input" 
                                                        />
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Error messages -->
                                    @error('output_labels') <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> @enderror
                                    @error('output_labels.*') <span class="text-xs text-red-600 dark:text-red-400 mt-1">All output labels are required</span> @enderror
                                    @error('output_data_types.*') <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> @enderror
                                    @error('output_min_values.*') <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> @enderror
                                    @error('output_max_values.*') <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Extra space at bottom -->
                                <div class="h-4"></div>
                            </div>
                        </form>
                    @endif
                </div>
                
                <!-- Footer -->
                <footer class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">
                    @if($viewMode && $selectedSensor)
                        <button @click="show = false" class="w-full px-5 py-3 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 sm:px-4 sm:py-2 sm:w-auto active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray">
                            Close
                        </button>
                    @else
                        <button @click="show = false" type="button" class="w-full px-5 py-3 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 sm:px-4 sm:py-2 sm:w-auto active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray">
                            Cancel
                        </button>
                        <button 
                            wire:click="store" 
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            wire:target="picture"
                            type="button" 
                            class="w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue disabled:opacity-50 disabled:cursor-not-allowed">
                            
                            <span wire:loading.remove wire:target="picture">{{ $sensorId ? 'Update' : 'Create' }}</span>
                            <span wire:loading wire:target="picture">
                                <svg class="inline w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    @endif
                </footer>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal tetap menggunakan entangle karena ada property showDeleteModal -->
    <div x-data="{ show: $wire.entangle('showDeleteModal') }" 
         x-show="show"
         x-cloak
         class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center">
        
        <div x-show="show"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 transform translate-y-1/2"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0 transform translate-y-1/2"
             @click.away="show = false"
             @keydown.escape="show = false"
             class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl">
            
            <!-- Modal header -->
            <header class="flex justify-between">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                    Are you sure?
                </h3>
                <button @click="show = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </header>
            
            <!-- Modal body -->
            <div class="mt-4 mb-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    This sensor will be deleted. This action cannot be undone.
                </p>
            </div>
            
            <!-- Modal footer -->
            <footer class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">
                <button wire:click="cancelDelete" class="w-full px-5 py-3 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 sm:px-4 sm:py-2 sm:w-auto active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray">
                    Cancel
                </button>
                <button wire:click="delete" class="w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red">
                    Delete
                </button>
            </footer>
        </div>
    </div>
    
    <style>
        /* Hide x-cloak elements until Alpine.js is loaded */
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Script untuk SweetAlert -->
    <script>
        window.addEventListener('swal:confirm', event => {
            Swal.fire({
                title: event.detail.title,
                text: event.detail.text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.delete(event.detail.id)
                    Swal.fire(
                        'Deleted!',
                        'Sensor has been deleted.',
                        'success'
                    )
                }
            });
        });
    </script>
</div>