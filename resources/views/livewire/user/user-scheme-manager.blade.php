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
                    placeholder="Search schemes..." 
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
        
        <!-- Schemes Table -->
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
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Visualization</th>
                            <th class="px-4 py-3">Columns & Sensors</th>
                            <th wire:click="sortBy('created_at')" class="px-4 py-3 cursor-pointer">
                                Created
                                @if ($sortField === 'created_at')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @forelse ($schemes as $scheme)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div>
                                            <p class="font-semibold">{{ $scheme->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm max-w-xs truncate">
                                    {{ $scheme->description ?? 'No description' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        if ($scheme->visualization_type == 'line') {
                                            $visStyle = 'background-color: #dcfce7; color: #16a34a;';
                                        } elseif ($scheme->visualization_type == 'bar') {
                                            $visStyle = 'background-color: #f3e8ff; color: #9333ea;';
                                        } elseif ($scheme->visualization_type == 'scatter') {
                                            $visStyle = 'background-color: #fef08a; color: #ca8a04;'; // contoh hex yellow
                                        } elseif ($scheme->visualization_type == 'pie') {
                                            $visStyle = 'background-color: #fecaca; color: #dc2626;';
                                        } elseif ($scheme->visualization_type == 'gauge') {
                                            $visStyle = 'background-color: #c7d2fe; color: #4338ca;'; // contoh hex indigo
                                        } else {
                                            $visStyle = 'background-color: #f3f4f6; color: #6b7280;';
                                        }
                                    @endphp
                                    <span style="{{ $visStyle }}" class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ ucfirst($scheme->visualization_type ?? 'line') }}
                                    </span>
                                </td>
                                <!-- Modified Columns Column (Combined with Sensors) -->
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        // Calculate number of sensor columns (outputs)
                                        $sensorColumns = 0;
                                        foreach($scheme->sensors as $sensor) {
                                            $sensorColumns += $sensor->num_of_outputs ?: 1; // Default to 1 if not specified
                                        }
                                        
                                        // Get additional columns count
                                        $additionalCount = is_array($scheme->additional_columns) ? count($scheme->additional_columns) : 0;
                                        
                                        // Calculate total
                                        $totalColumns = $sensorColumns + $additionalCount;
                                        
                                        // Get colors for badge
                                        $badgeColors = [
                                            ['bg' => '#dbeafe', 'text' => '#1e40af'], // blue
                                            ['bg' => '#dcfce7', 'text' => '#15803d'], // green
                                            ['bg' => '#fff7ed', 'text' => '#9a3412'], // orange
                                            ['bg' => '#fef2f2', 'text' => '#991b1b'], // red
                                            ['bg' => '#f3f4f6', 'text' => '#6b7280'], // gray
                                        ];
                                        
                                        // Count how many total items we have
                                        $sensorCount = $scheme->sensors()->count();
                                        $totalItems = $sensorCount + $additionalCount;
                                        
                                        // Determine badges to show (max 2 total)
                                        $displayedBadges = 0;
                                        $remainingCount = $totalItems;
                                    @endphp
                                    
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $totalColumns }} total columns</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $sensorColumns }} from {{ $sensorCount }} sensors
                                            @if($additionalCount > 0)
                                                + {{ $additionalCount }} additional
                                            @endif
                                        </span>
                                        
                                        <!-- Combined badges - maximum 2 total -->
                                        <div class="flex flex-wrap mt-2" style="gap: 0.25rem;">
                                            @php $remainingBadges = 2; @endphp
                                            
                                            <!-- Sensor badges - prioritized first -->
                                            @foreach($scheme->sensors()->take($remainingBadges)->get() as $sensor)
                                                @php
                                                    $hash = crc32($sensor->name);
                                                    $index = $hash % count($badgeColors);
                                                    $sensorColor = $badgeColors[$index];
                                                    $remainingBadges--;
                                                @endphp
                                                <span style="background-color: {{ $sensorColor['bg'] }}; color: {{ $sensorColor['text'] }};" class="px-2 py-0.5 text-xs font-medium rounded-full">
                                                    {{ $sensor->name }}{{ $sensor->pivot->alias ? ' (' . $sensor->pivot->alias . ')' : '' }}
                                                </span>
                                            @endforeach
                                            
                                            <!-- Additional columns badges - show only if we have remaining badge slots -->
                                            @if($remainingBadges > 0 && $additionalCount > 0)
                                                @foreach(array_slice($scheme->additional_columns, 0, $remainingBadges) as $column)
                                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                        {{ $column['name'] }}
                                                    </span>
                                                    @php $remainingBadges--; @endphp
                                                @endforeach
                                            @endif
                                            
                                            <!-- More indicator - show if there are more than 2 total items -->
                                            @if($totalItems > 2)
                                                <div x-data="{ showMore: false }" class="relative inline-block">
                                                    <span 
                                                        @mouseenter="showMore = true" 
                                                        @mouseleave="showMore = false"
                                                        class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 cursor-help"
                                                    >
                                                        +{{ $totalItems - (2 - $remainingBadges) }} more
                                                    </span>
                                                    
                                                    <!-- Hover Popup -->
                                                    <div 
                                                        x-show="showMore" 
                                                        x-transition:enter="transition ease-out duration-200" 
                                                        x-transition:enter-start="opacity-0 transform scale-95" 
                                                        x-transition:enter-end="opacity-100 transform scale-100" 
                                                        x-transition:leave="transition ease-in duration-100" 
                                                        x-transition:leave-start="opacity-100 transform scale-100" 
                                                        x-transition:leave-end="opacity-0 transform scale-95"
                                                        @mouseenter="showMore = true" 
                                                        @mouseleave="showMore = false"
                                                        class="absolute z-50 w-64 p-3 text-sm bg-white rounded-lg shadow-lg dark:bg-gray-800 border border-gray-200 dark:border-gray-700" 
                                                        style="bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: 8px;"
                                                    >
                                                        <div class="max-h-48 overflow-y-auto">
                                                            <!-- Hidden Sensors -->
                                                            @if($scheme->sensors()->count() > 2)
                                                                <div class="mb-2">
                                                                    <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Sensors:</h4>
                                                                    <div class="flex flex-wrap mt-1" style="gap: 0.25rem;">
                                                                        @foreach($scheme->sensors()->skip(2)->get() as $sensor)
                                                                            @php
                                                                                $hash = crc32($sensor->name);
                                                                                $index = $hash % count($badgeColors);
                                                                                $sensorColor = $badgeColors[$index];
                                                                            @endphp
                                                                            <span style="background-color: {{ $sensorColor['bg'] }}; color: {{ $sensorColor['text'] }};" class="px-2 py-0.5 text-xs font-medium rounded-full">
                                                                                {{ $sensor->name }}
                                                                            </span>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            
                                                            <!-- Hidden Additional Columns -->
                                                            @if($additionalCount > 2 - min(2, $scheme->sensors()->count()))
                                                                <div>
                                                                    <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Additional Columns:</h4>
                                                                    <div class="flex flex-wrap gap-1 mt-1" style="gap: 0.25rem;">
                                                                        @foreach(array_slice($scheme->additional_columns, max(0, min(2, $remainingBadges))) as $column)
                                                                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                                                {{ $column['name'] }}
                                                                            </span>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        
                                                        <!-- Arrow Pointer -->
                                                        <div class="absolute w-3 h-3 bg-white dark:bg-gray-800 border-r border-b border-gray-200 dark:border-gray-700 transform rotate-45" style="bottom: -7px; left: 50%; margin-left: -6px;"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $scheme->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center space-x-2 justify-center">
                                        <button
                                            wire:click="view({{ "'" . $scheme->id . "'" }})"
                                            class="p-1 text-blue-600 rounded-full dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900 focus:outline-none focus:shadow-outline-blue"
                                            aria-label="View"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="edit({{ "'" . $scheme->id . "'" }})"
                                            class="p-1 text-green-600 rounded-full dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900 focus:outline-none focus:shadow-outline-green"
                                            aria-label="Edit"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="confirmDelete({{ "'" . $scheme->id . "'" }})"
                                            class="p-1 text-red-600 rounded-full dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900 focus:outline-none focus:shadow-outline-red"
                                            aria-label="Delete"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                        <a
                                            href="{{ route('scheme.show', $scheme->id) }}"
                                            class="p-1 text-orange-600 rounded-full dark:text-orange-400 hover:bg-orange-100 dark:hover:bg-orange-900 focus:outline-none focus:shadow-outline-orange"
                                            aria-label="View Data"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    {{ $search ? 'No schemes found matching "' . $search . '"' : 'No schemes available. Create your first one!' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 bg-white border-t dark:bg-gray-800 dark:border-gray-700">
                {{ $schemes->links() }}
            </div>
        </div>
    </div>
    
    <!-- Modal untuk form scheme -->
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
                        {{ $viewMode ? 'Scheme Details' : ($schemeId ? 'Edit Scheme' : 'Create Scheme') }}
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
                    @if($viewMode && $selectedScheme)
                        <!-- View mode content - Updated to separate sensors and additional columns -->
<div>
    <!-- Scheme ID and Name -->
    <div class="mb-4">
        <div class="flex justify-between items-center">
            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300">{{ $selectedScheme->name }}</h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $selectedScheme->id }}</span>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $selectedScheme->description ?? 'No description' }}</p>
    </div>
    
    <!-- Summary of Columns -->
    <div class="mb-4">
        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Columns Summary</h4>
        
        @php
            // Calculate number of sensor columns (outputs)
            $sensorColumns = 0;
            foreach($selectedScheme->sensors as $sensor) {
                $sensorColumns += $sensor->num_of_outputs ?: 1;
            }
            
            // Get additional columns count
            $additionalCount = is_array($selectedScheme->additional_columns) ? count($selectedScheme->additional_columns) : 0;
            
            // Calculate total
            $totalColumns = $sensorColumns + $additionalCount;
        @endphp
        
        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <span class="block font-medium">{{ $totalColumns }} total columns</span>
            <span class="block text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ $sensorColumns }} from {{ $selectedScheme->sensors->count() }} sensors
                @if($additionalCount > 0)
                    + {{ $additionalCount }} additional
                @endif
            </span>
        </div>
    </div>
    
    <!-- Visualization Type -->
    <div class="mb-4">
        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Visualization Type</h4>
        @php
            if ($selectedScheme->visualization_type == 'line') {
                $visStyle = 'background-color: #dcfce7; color: #16a34a;';
            } elseif ($selectedScheme->visualization_type == 'bar') {
                $visStyle = 'background-color: #f3e8ff; color: #9333ea;';
            } elseif ($selectedScheme->visualization_type == 'scatter') {
                $visStyle = 'background-color: #fef08a; color: #ca8a04;'; // yellow
            } elseif ($selectedScheme->visualization_type == 'pie') {
                $visStyle = 'background-color: #fecaca; color: #dc2626;';
            } elseif ($selectedScheme->visualization_type == 'gauge') {
                $visStyle = 'background-color: #c7d2fe; color: #4338ca;'; // indigo
            } else {
                $visStyle = 'background-color: #f3f4f6; color: #6b7280;';
            }
        @endphp
        <span style="{{ $visStyle }}" class="px-2 py-1 text-xs font-medium rounded-full">
            {{ ucfirst($selectedScheme->visualization_type ?? 'line') }}
        </span>
    </div>
    
    <!-- Sensor Section with Detailed Information -->
    <div class="mb-4">
        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Sensors ({{ $selectedScheme->sensors->count() }})</h4>
        
        @if($selectedScheme->sensors->count() > 0)
            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                <!-- In the sensor table in view mode -->
<table class="w-full text-sm">
    <thead>
        <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
            <th class="text-left pb-2">Name</th>
            <th class="text-center pb-2">Alias</th>
            <th class="text-center pb-2">Order</th>
            <th class="text-center pb-2">Outputs</th>
        </tr>
    </thead>
    <tbody>
        @foreach($selectedScheme->sensors as $sensor)
            <tr class="border-b border-gray-100 dark:border-gray-700">
                <td class="py-2">{{ $sensor->name }}</td>
                <td class="text-center py-2">{{ $sensor->pivot->alias ?? '-' }}</td>
                <td class="text-center py-2">{{ $loop->index + 1 }}</td>
                <td class="text-center py-2">{{ $sensor->num_of_outputs ?? 1 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
                
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                    The order determines how raw data values will be mapped to sensors during API submissions.
                </p>
            </div>
        @else
            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                <span class="text-gray-500 dark:text-gray-400">No sensors assigned to this scheme.</span>
            </div>
        @endif
    </div>
    
    <!-- Additional Columns Section with Detailed Information -->
    <div class="mb-4">
        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Additional Columns ({{ $additionalCount }})</h4>
        
        @if($additionalCount > 0)
            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                            <th class="text-left pb-2">Name</th>
                            <th class="text-center pb-2">Type</th>
                            <th class="text-center pb-2">Required</th>
                            <th class="text-right pb-2">Default Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedScheme->additional_columns as $column)
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="py-2">
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $column['name'] }}
                                    </span>
                                </td>
                                <td class="text-center py-2">{{ ucfirst($column['data_type'] ?? 'string') }}</td>
                                <td class="text-center py-2">
                                    @if(isset($column['is_required']) && $column['is_required'])
                                        <span class="text-green-600 dark:text-green-400">Yes</span>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">No</span>
                                    @endif
                                </td>
                                <td class="text-right py-2">{{ $column['default_value'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                    Additional columns are used for metadata when submitting sensor readings via API.
                </p>
            </div>
        @else
            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                <span class="text-gray-500 dark:text-gray-400">No additional columns defined for this scheme.</span>
            </div>
        @endif
    </div>
    
    <!-- API Information Section -->
    <div class="mb-4">
        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">API Information</h4>
        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                Send data to this scheme using the following API endpoint:
            </p>
            <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg overflow-x-auto">
            <pre class="text-xs text-gray-700 dark:text-gray-300">
<code>POST /api/data{
    "api_key": "your_token",
    "scheme_id": {{ $selectedScheme->id }},
    "values": "",
    "additional_values": { 
        @if($additionalCount > 0)@foreach($selectedScheme->additional_columns as $index => $column)    "{{ $column['name'] }}": "{{ $column['default_value'] ?? 'value' }}"{{ $index < count($selectedScheme->additional_columns) - 1 ? ',' : ' ' }}
        @endforeach@else// No additional columns defined @endif
    }
}</code></pre>
            </div>
        </div>
    </div>
    
    <!-- Timestamps -->
    <div class="mb-2">
        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Timestamps</h4>
        <div class="bg-gray-50 dark:bg-gray-700 px-3 rounded-lg">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Created:</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $selectedScheme->created_at->format('F j, Y H:i:s') }}</p>
                </div>
                <div class="mb-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Last Updated:</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $selectedScheme->updated_at->format('F j, Y H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
                    @else
                        <!-- Edit/Create mode content -->
<form id="schemeForm" wire:submit.prevent="store">
    <div class="grid gap-6 mb-6 md:grid-cols-2">
        <!-- Name field - Selalu dapat diedit -->
        <div class="col-span-2">
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
            <input wire:model="name" type="text" id="name" class="px-2 py-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-gray-500" required>
            @error('name')
                <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <!-- Description field - Selalu dapat diedit -->
        <div class="col-span-2">
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
            <textarea wire:model="description" id="description" rows="3" class="px-2 py-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-gray-500"></textarea>
            @error('description')
                <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <!-- Sensors selection - Hanya aktif saat CREATE, readonly saat EDIT -->
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">
                Sensors
                @if($schemeId)
                    <span class="text-xs text-amber-600 dark:text-amber-400 ml-2">(Cannot be modified after creation)</span>
                @endif
            </label>
            
            <!-- Sensor Selection Section - Replace existing sensor dropdown -->
@if(!$schemeId)
    <!-- Tampilkan sensor selection hanya jika mode CREATE -->
    <div class="space-y-4">
        @foreach($pendingSensors as $index => $pendingSensorId)
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                <div class="flex items-start gap-4">
                    <!-- Sensor Selection -->
                    <div class="flex-grow">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">
                            Select Sensor {{ $index + 1 }}
                        </label>
                        
                        <!-- Custom Sensor Selector with Images -->
                        <div class="relative" x-data="{ 
                            open: false, 
                            selected: @entangle('pendingSensors.' . $index),
                            sensors: @js($sensors),
                            getSensorById(id) {
                                return this.sensors.find(s => s.id == id) || null;
                            },
                            getSelectedSensor() {
                                return this.getSensorById(this.selected);
                            }
                        }">
                            <!-- Selected Display -->
                            <div @click="open = !open" 
                                 class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
                                <div x-show="!selected" class="text-gray-500 dark:text-gray-400">
                                    Click to select a sensor...
                                </div>
                                <div x-show="selected" class="flex items-center gap-3">
                                    <!-- Sensor Image -->
                                    <div class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded-lg overflow-hidden flex-shrink-0">
                                        <template x-if="getSelectedSensor()?.picture">
                                            <img :src="'/storage/' + getSelectedSensor().picture" 
                                                 :alt="getSelectedSensor()? getSelectedSensor().name : ''"
                                                 class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!getSelectedSensor()?.picture">
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <!-- Sensor Info -->
                                    <div class="flex-grow min-w-0">
                                        <div class="font-medium text-gray-900 dark:text-gray-100" x-text="getSelectedSensor()?.name"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                            <span x-text="getSelectedSensor()?.description || 'No description'"></span>
                                        </div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500">
                                            <span x-text="(getSelectedSensor()?.num_of_outputs || 1) + ' output(s)'"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Dropdown Arrow -->
                                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Dropdown Options -->
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 @click.away="open = false"
                                 class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                                
                                <!-- Clear Selection Option -->
                                <div @click="selected = null; open = false" 
                                     class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-200 dark:border-gray-600">
                                    <div class="text-gray-500 dark:text-gray-400 italic">Clear selection</div>
                                </div>
                                
                                <!-- Sensor Options -->
                                <template x-for="sensor in sensors" :key="sensor.id">
                                    <div @click="selected = sensor.id; open = false; $wire.set('pendingSensors.{{ $index }}', sensor.id)" 
                                         class="px-3 py-3 hover:bg-blue-50 dark:hover:bg-blue-900 cursor-pointer flex items-center gap-3"
                                         :class="{'bg-blue-100 dark:bg-blue-800': selected == sensor.id}">
                                        
                                        <!-- Sensor Image -->
                                        <div class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-lg overflow-hidden flex-shrink-0">
                                            <template x-if="sensor.picture">
                                                <img :src="'/storage/' + sensor.picture" 
                                                     :alt="sensor.name"
                                                     class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!sensor.picture">
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            </template>
                                        </div>
                                        
                                        <!-- Sensor Details -->
                                        <div class="flex-grow min-w-0">
                                            <div class="font-medium text-gray-900 dark:text-gray-100" x-text="sensor.name"></div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400 truncate" x-text="sensor.description || 'No description'"></div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="(sensor.num_of_outputs || 1) + ' output(s)'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alias Input -->
                    <div class="w-1/3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">
                            Alias (Optional)
                        </label>
                        <input 
                            wire:model.live="sensorAliases.{{ $index }}" 
                            type="text" 
                            placeholder="Custom name..." 
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                        >
                    </div>
                    
                    <!-- Remove Button -->
                    @if(count($pendingSensors) > 1)
                        <div class="flex items-end">
                            <button 
                                wire:click.prevent="removePendingSensor({{ $index }})"
                                type="button"
                                class="p-2 text-red-600 rounded-full dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900 focus:outline-none focus:shadow-outline-red"
                                title="Remove sensor"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
                
                <!-- Output Labels Preview -->
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600" x-data="{ 
                    sensorId: @entangle('pendingSensors.' . $index),
                    sensors: @js($sensors),
                    getSensor() {
                        return this.sensors.find(s => s.id == this.sensorId) || null;
                    },
                    getOutputLabels() {
                        const sensor = this.getSensor();
                        if (!sensor || !sensor.output_labels) return [];
                        return sensor.output_labels.split(',').map(label => label.trim()).filter(label => label);
                    }
                }" x-show="sensorId">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Output columns for this sensor:</div>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="(label, idx) in getOutputLabels()" :key="idx">
                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs" x-text="label || `Value ${idx + 1}`"></span>
                        </template>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Add Sensor Button -->
    <div class="mt-4">
        <button 
            wire:click.prevent="addSensorDropdown" 
            type="button"
            class="inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Another Sensor
        </button>
    </div>
@else
    <!-- Display readonly sensors if mode EDIT - Enhanced with images -->
    <div class="space-y-3">
        @if($selectedScheme && $selectedScheme->sensors && $selectedScheme->sensors->count() > 0)
            @foreach($selectedScheme->sensors as $sensor)
                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg flex items-center gap-3">
                    <!-- Sensor Image -->
                    <div class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-lg overflow-hidden flex-shrink-0">
                        @if($sensor->picture)
                            <img src="{{ asset('storage/' . $sensor->picture) }}" 
                                 alt="{{ $sensor->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Sensor Info -->
                    <div class="flex-grow">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $sensor->name }}</span>
                            @if($sensor->pivot->alias)
                                <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs">
                                    {{ $sensor->pivot->alias }}
                                </span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $sensor->description ?? 'No description' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Order: #{{ $loop->index + 1 }} • {{ $sensor->num_of_outputs ?? 1 }} output(s)
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg text-center">
                <span class="text-gray-500 dark:text-gray-400">No sensors assigned to this scheme.</span>
            </div>
        @endif
    </div>
@endif

            @error('pendingSensors')
                <span class="text-xs text-red-600 dark:text-red-400 mt-2 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Additional Columns - Hanya aktif saat CREATE, readonly saat EDIT -->
        <div class="col-span-2 mt-2 mb-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">
                Additional Columns
                @if($schemeId)
                    <span class="text-xs text-amber-600 dark:text-amber-400 ml-2">(Cannot be modified after creation)</span>
                @endif
            </label>
            
            @if(!$schemeId)
                <!-- Show editable additional columns only in CREATE mode -->
                <div class="space-y-3">
                    @foreach($additionalColumns as $index => $column)
                        <div class="flex gap-2 items-start mb-2" style="gap: 0.5rem;">
                            <div class="flex-1">
                                <input 
                                    wire:model.live="additionalColumns.{{ $index }}.name" 
                                    type="text" 
                                    placeholder="Column name" 
                                    class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500"
                                >
                            </div>
                            
                            <div class="flex-1">
                                <select 
                                    wire:model.live="additionalColumns.{{ $index }}.data_type" 
                                    class="block p-2 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500"
                                >
                                    <option value="">-- Type --</option>
                                    <option value="string">Text</option>
                                    <option value="number">Number</option>
                                    <option value="boolean">Yes/No</option>
                                    <option value="date">Date</option>
                                </select>
                            </div>
                            
                            <div class="flex-1">
                                <input 
                                    wire:model.live="additionalColumns.{{ $index }}.default_value" 
                                    type="text" 
                                    placeholder="Default value" 
                                    class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500"
                                >
                            </div>
                            
                            <div class="py-2">
                                <label class="flex items-center">
                                    <input 
                                        wire:model.live="additionalColumns.{{ $index }}.is_required"
                                        type="checkbox" 
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700"
                                    >
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Required</span>
                                </label>
                            </div>
                            
                            <div>
                                <button 
                                    wire:click.prevent="removeAdditionalColumn({{ $index }})"
                                    type="button"
                                    class="p-2 text-red-600 rounded-full dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900 focus:outline-none focus:shadow-outline-red"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Add Column Button -->
                <div class="mt-2">
                    <button 
                        wire:click.prevent="addAdditionalColumn" 
                        type="button"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Column
                    </button>
                </div>
            @else
                <!-- Display readonly additional columns if mode EDIT -->
                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                    @if(count($additionalColumns) > 0)
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                                    <th class="text-left pb-2">Name</th>
                                    <th class="text-center pb-2">Type</th>
                                    <th class="text-center pb-2">Required</th>
                                    <th class="text-right pb-2">Default Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($additionalColumns as $column)
                                    <tr class="border-b border-gray-100 dark:border-gray-700">
                                        <td class="py-2">{{ $column['name'] ?? 'Unnamed' }}</td>
                                        <td class="text-center py-2">{{ ucfirst($column['data_type'] ?? 'string') }}</td>
                                        <td class="text-center py-2">
                                            @if(isset($column['is_required']) && $column['is_required'])
                                                <span class="text-green-600 dark:text-green-400">Yes</span>
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">No</span>
                                            @endif
                                        </td>
                                        <td class="text-right py-2">{{ $column['default_value'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <span class="text-gray-500 dark:text-gray-400">No additional columns defined.</span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Visualization Type - Hanya aktif saat CREATE, readonly saat EDIT -->
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">
                Visualization Type
                @if($schemeId)
                    <span class="text-xs text-amber-600 dark:text-amber-400 ml-2">(Cannot be modified after creation)</span>
                @endif
            </label>
            
            @if(!$schemeId)
                <!-- Show visualization options only in CREATE mode -->
                <div class="flex space-x-3">
                    <!-- Line Chart (timeseries) option -->
                    <label class="visualization-option flex-1">
                        <input type="radio" wire:model.live="visualizationType" value="line" class="peer sr-only">
                        <div class="flex flex-col items-center p-2 border-2 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900 dark:peer-checked:border-blue-500 border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900">
                            <svg class="w-6 h-6 mb-1 peer-checked:text-blue-600 dark:peer-checked:text-blue-400 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4v16"></path>
                            </svg>
                            <span class="text-xs font-medium peer-checked:text-blue-600 dark:peer-checked:text-blue-400 text-gray-500 dark:text-gray-400">Line Chart</span>
                        </div>
                    </label>
                    
                    <!-- Bar Chart option -->
                    <label class="visualization-option flex-1">
                        <input type="radio" wire:model.live="visualizationType" value="bar" class="peer sr-only">
                        <div class="flex flex-col items-center p-2 border-2 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900 dark:peer-checked:border-blue-500 border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900">
                            <svg class="w-6 h-6 mb-1 peer-checked:text-blue-600 dark:peer-checked:text-blue-400 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4v16"></path>
                            </svg>
                            <span class="text-xs font-medium peer-checked:text-blue-600 dark:peer-checked:text-blue-400 text-gray-500 dark:text-gray-400">Bar Chart</span>
                        </div>
                    </label>
                    
                    <!-- Scatter Plot option -->
                    <label class="visualization-option flex-1">
                        <input type="radio" wire:model.live="visualizationType" value="scatter" class="peer sr-only">
                        <div class="flex flex-col items-center p-2 border-2 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900 dark:peer-checked:border-blue-500 border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900">
                            <svg class="w-6 h-6 mb-1 peer-checked:text-blue-600 dark:peer-checked:text-blue-400 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12a2 2 0 100-4 2 2 0 000 4zM18 8a2 2 0 100-4 2 2 0 000 4zM14 16a2 2 0 100-4 2 2 0 000 4zM10 18a2 2 0 100-4 2 2 0 000 4z"></path>
                            </svg>
                            <span class="text-xs font-medium peer-checked:text-blue-600 dark:peer-checked:text-blue-400 text-gray-500 dark:text-gray-400">Scatter Plot</span>
                        </div>
                    </label>
                </div>
            @else
                <!-- Display readonly visualization type if mode EDIT -->
                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                    @php
                        $visualizationLabel = ucfirst($visualizationType ?? 'line');
                        
                        if ($visualizationType == 'line') {
                            $visStyle = 'background-color: #dcfce7; color: #16a34a;';
                        } elseif ($visualizationType == 'bar') {
                            $visStyle = 'background-color: #f3e8ff; color: #9333ea;';
                        } elseif ($visualizationType == 'scatter') {
                            $visStyle = 'background-color: #fef08a; color: #ca8a04;';
                        } elseif ($visualizationType == 'pie') {
                            $visStyle = 'background-color: #fecaca; color: #dc2626;';
                        } elseif ($visualizationType == 'gauge') {
                            $visStyle = 'background-color: #c7d2fe; color: #4338ca;';
                        } else {
                            $visStyle = 'background-color: #f3f4f6; color: #6b7280;';
                        }
                    @endphp
                    <span style="{{ $visStyle }}" class="px-2 py-1 text-xs font-medium rounded-full">
                        {{ $visualizationLabel }}
                    </span>
                </div>
            @endif
            
            @error('visualizationType')
                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>
    </div>
</form>
                    @endif
                </div>
                
                <!-- Modal footer -->
                <footer class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">
                    @if($viewMode && $selectedScheme)
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
                            {{ $schemeId ? 'Update' : 'Create' }}
                        </button>
                    @endif
                </footer>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
        x-data="{ show: @entangle('showDeleteModal').live }"
        x-cloak
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
                            Delete Scheme
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
                        Are you sure you want to delete this scheme? All associated data will be inaccessible. This action cannot be undone.
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
    
        .visualization-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        
        .visualization-option input[type="radio"] + div {
            height: 100%;
            width: 100%;
            transition: all 0.1s ease-in-out;
        }
        
        .visualization-option input[type="radio"]:checked + div {
            border-color: rgb(59, 130, 246);
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        .visualization-option input[type="radio"]:focus + div {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
        
        .dark .visualization-option input[type="radio"]:checked + div {
            border-color: rgb(59, 130, 246);
            background-color: rgba(59, 130, 246, 0.2);
        }
    </style>
</div>
