<!-- filepath: c:\Users\Aji\Documents\raspiot\resources\views\livewire\user\user-scheme-dashboard.blade.php -->
<div class="dark:bg-gray-900">
    <!-- Scheme Header Card -->
    <div class="px-4 py-3 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                    {{ $scheme->name }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $scheme->description ?? 'No description' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Auto Refresh Controls -->
                <div class="flex items-center gap-2">
                    <label class="flex items-center mr-2">
                        <input 
                            type="checkbox" 
                            wire:model.live="autoRefresh"
                            class="form-checkbox h-4 w-4 text-blue-600 rounded focus:ring-blue-500 focus:ring-2"
                        >
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Auto Refresh</span>
                    </label>
                    
                    @if($autoRefresh)
                        <select 
                            wire:model.live="refreshInterval"
                            class="text-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                            <option value="5">5s</option>
                            <option value="10">10s</option>
                            <option value="30">30s</option>
                            <option value="60">1m</option>
                            <option value="120">2m</option>
                            <option value="300">5m</option>
                        </select>
                    @endif
                    
                    <!-- Manual Refresh Button -->
                    <button wire:click="manualRefresh" title="Click for Manual Refresh"
                            class="px-3 ml-2 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-green-600 border border-transparent rounded-lg hover:bg-green-700 focus:outline-none focus:shadow-outline-green">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
                
                <a href="{{ route('user.schemes') }}" class="px-4 py-2 ml-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                    Back to Schemes
                </a>
            </div>
        </div>
    </div>
    
    <!-- Status Indicator yang responsive -->
    <div class="px-4 py-3 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Keterangan Range, Agregasi, Jumlah Data -->
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    @if($timeRange === 'custom')
                        {{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                    @else
                        @php
                            $rangeText = match($timeRange) {
                                '24h' => 'Last 24 hours',
                                '7d' => 'Last 7 days', 
                                '30d' => 'Last 30 days',
                                'all' => 'All available data',
                                default => 'Last 24 hours'
                            };
                        @endphp
                        {{ $rangeText }}
                        @if($timeRange === 'all')
                            - {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                        @endif
                    @endif

                    @if($dataAggregation !== 'raw')
                        | 
                        @php
                            $aggregationLabel = match($dataAggregation) {
                                'hourly' => 'Hourly',
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly',
                                default => ucfirst($dataAggregation)
                            };
                        @endphp
                        {{ $aggregationLabel }} {{ strtoupper($aggregationFunction) }}
                    @endif

                    @if(count($processedData) > 0)
                        | {{ count($processedData) }} {{ $dataAggregation === 'raw' ? 'records' : 'periods' }}
                        @if($dataAggregation !== 'raw' && isset($processedData[0]['data_count']))
                            | {{ collect($processedData)->sum('data_count') }} total records
                        @endif
                    @endif
                </div>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                (Last updated: <span id="lastUpdated">{{ now()->format('H:i:s') }})</span>
            </div>
        </div>
    </div>

    <!-- Chart Card -->
    <div class="px-4 py-3 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800 chart-container" style="height:65vh">
        <!-- Integrated Header with Time Range Controls -->
        <div class="flex flex-wrap justify-between items-center mb-4 chart-controls">
            <!-- Title -->
            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300">Data Visualization</h3>
            
            <!-- Time Range Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center">
                    <label class="text-sm text-gray-600 dark:text-gray-400 mr-2">Time Range:</label>
                    <select wire:model.live="timeRange" class="w-32 px-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="24h">Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                        <option value="all">All Data</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                
                @if($timeRange === 'custom')
                    <div class="flex items-center" style="gap: 0.5rem;">
                        <div class="flex items-center">
                            <label class="text-sm text-gray-600 dark:text-gray-400 mr-2">From:</label>
                            <input 
                                type="date" 
                                wire:model="dateFrom" 
                                max="{{ now()->format('Y-m-d') }}"
                                class="w-32 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                        </div>
                        
                        <div class="flex items-center">
                            <label class="text-sm text-gray-600 dark:text-gray-400 mr-2">To:</label>
                            <input 
                                type="date" 
                                wire:model="dateTo" 
                                max="{{ now()->format('Y-m-d') }}"
                                min="{{ $dateFrom }}"
                                class="w-32 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                        </div>
                        
                        <button 
                            wire:click="applyCustomDateRange" 
                            class="px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            Apply
                        </button>
                    </div>
                @endif
                
                <!-- Data Aggregation Controls -->
                <div class="flex items-center gap-3 ml-4 pl-4 border-l border-gray-300 dark:border-gray-600">
                    <div class="flex items-center">
                        <label class="text-sm text-gray-600 dark:text-gray-400 mr-2">Data View:</label>
                        <select wire:model.live="dataAggregation" class="w-28 px-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="raw">Raw Data</option>
                            <option value="hourly">Hourly</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                        </select>
                    </div>
                    
                    @if($dataAggregation !== 'raw')
                        <div class="flex items-center">
                            <label class="text-sm text-gray-600 dark:text-gray-400 mr-2">Function:</label>
                            <select wire:model.live="aggregationFunction" class="w-20 px-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="avg">AVG</option>
                                <option value="median">MEDIAN</option>
                            </select>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if(empty($processedData))
            <div class="p-4 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-lg">
                No data available for the selected time range.
            </div>
        @else
            <div class="chart-area">
                <div wire:ignore class="w-full h-full" id="iotChartNumeric" style="height: 62.5vh;"></div>
            </div>
        @endif
    </div>
    
    <!-- Data Table Card -->
    <div class="px-4 py-3 mb-2 mt-2 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4 px-2">
            <!-- Data table title with aggregation info -->
            <div class="flex items-center">
                <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300">
                    @if($dataAggregation === 'raw')
                        Raw Data
                    @else
                        @if($showRawData)
                            Raw Data
                        @else
                            {{ ucfirst($dataAggregation) }} Aggregated Data 
                            ({{ $aggregationFunction === 'avg' ? 'AVG' : 'MEDIAN' }})
                        @endif
                    @endif
                </h3>
                
                <!-- Toggle between aggregated and raw data view -->
                @if($dataAggregation !== 'raw')
                    <div class="flex items-center ml-4">
                        <button 
                            wire:click="toggleDataView" 
                            class="px-3 py-1 text-xs font-medium leading-5 {{ $showRawData ? 'text-blue-600 bg-blue-50 border-blue-600' : 'text-gray-600 bg-gray-50 border-gray-600' }} border rounded-md hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                        >
                            {{ $showRawData ? 'Show Aggregated' : 'Show Raw Data' }}
                        </button>
                    </div>
                @endif
                
                <!-- Per Page Selector -->
                <div class="flex items-center ml-4">
                    <label class="text-sm text-gray-600 dark:text-gray-400 mr-2">Records per page:</label>
                    <select wire:model.live="perPage" class="w-16 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            
            <!-- Export Buttons -->
            <div class="flex items-center" style="gap: 0.5rem;">
                <!-- CSV button with green outline -->
                <button wire:click="exportData('csv')" class="px-3 py-1 text-sm font-medium leading-5 text-green-600 bg-white border border-green-600 rounded-md hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Export CSV
                    @if($dataAggregation !== 'raw' && !$showRawData)
                        <span class="text-xs">
                            ({{ $aggregationFunction === 'avg' ? 'AVG' : 'MEDIAN' }})
                        </span>
                    @endif
                </button>
                
                <!-- Excel button with Excel green background -->
                <button wire:click="exportData('xlsx')" class="px-3 py-1 text-sm font-medium leading-5 text-white border border-transparent rounded-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-green-500" style="background-color: #1D6F42;">
                    Export Excel
                    @if($dataAggregation !== 'raw' && !$showRawData)
                        <span class="text-xs">
                            ({{ $aggregationFunction === 'avg' ? 'AVG' : 'MEDIAN' }})
                        </span>
                    @endif
                </button>
            </div>
        </div>
        
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                        @if($dataAggregation === 'raw' || $showRawData)
                            <th class="px-2 py-3">Time</th>
                        @endif
                        
                        @if($dataAggregation !== 'raw' && !$showRawData)
                            <th class="px-4 py-3">Period</th>
                            <th class="px-4 py-3">Data Points</th>
                        @endif
                        @php $sensorIdx = 0; @endphp
                        @foreach($scheme->sensors as $sensor)
                            @php
                                $outputs = $sensor->num_of_outputs ?: 1;
                                $outputLabels = explode(',', $sensor->output_labels ?? '');
                            @endphp
                            
                            @for($i = 0; $i < $outputs; $i++)
                                <th class="px-4 py-3">
                                    {{ $sensor->pivot->alias ?: $sensor->name }}
                                    @if($outputs > 1)
                                        ({{ isset($outputLabels[$i]) ? $outputLabels[$i] : "Output ".($i+1) }})
                                    @endif
                                    @if($dataAggregation !== 'raw' && !$showRawData)
                                        <span class="text-xs text-blue-600">
                                            ({{ $aggregationFunction === 'avg' ? 'AVG' : 'MEDIAN' }})
                                        </span>
                                    @endif
                                </th>
                            @endfor
                        @endforeach
                        
                        @if(is_array($scheme->additional_columns) && count($scheme->additional_columns) > 0)
                            @foreach($scheme->additional_columns as $column)
                                <th class="px-4 py-3">{{ $column['name'] }}</th>
                            @endforeach
                        @endif
                    </tr>
                </thead>
                @php
                $sensorOutputTypes = [];
                foreach ($scheme->sensors as $sensor) {
                    $types = [];
                    $validation = [];
                    try {
                        $validation = $sensor->validation_settings ? json_decode($sensor->validation_settings, true) : [];
                    } catch (\Throwable $e) {
                        $validation = [];
                    }
                    for ($i = 0; $i < ($sensor->num_of_outputs ?? 1); $i++) {
                        $types[] = $validation[$i]['type'] ?? 'number';
                    }
                    $sensorOutputTypes[] = $types;
                }
                @endphp
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    @if($dataAggregation !== 'raw' && !$showRawData)
                        {{-- Display aggregated data --}}
                        @forelse($paginatedData as $data)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-4 py-3 text-sm font-medium">
                                    {{ $data['period'] }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $data['data_count'] }} records
                                </td>

                                @foreach($scheme->sensors as $sensor)
                                    @php
                                        $outputs = $sensor->num_of_outputs ?: 1;
                                        $outputLabels = explode(',', $sensor->output_labels ?? '');

                                        // Find sensor data in aggregated data
                                        $pivotAlias = $sensor->pivot->alias ?? null;
                                        foreach ($data['sensors'] as $aggSensor) {
                                            if (
                                                $aggSensor['id'] == $sensor->id &&
                                                (
                                                    (isset($aggSensor['alias']) && $aggSensor['alias'] == $pivotAlias) ||
                                                    (!isset($aggSensor['alias']) && !$pivotAlias)
                                                )
                                            ) {
                                                $sensorData = $aggSensor;
                                                break;
                                            }
                                        }
                                    @endphp

                                    @for($i = 0; $i < $outputs; $i++)
                                        @php
                                            $label = isset($outputLabels[$i]) ? trim($outputLabels[$i]) : "Value " . ($i + 1);
                                            $type = $sensorOutputTypes[$sensorIdx][$i] ?? 'number';
                                            $value = $sensorData && isset($sensorData['values'][$label]) ? $sensorData['values'][$label] : '-';
                                            if (is_numeric($value)) {
                                                if ($type === 'percentage') {
                                                    $value = number_format($value * 100, 2) . '%';
                                                } else {
                                                    $value = number_format($value, 2);
                                                }
                                            }
                                        @endphp
                                        <td class="px-4 py-3 text-sm">
                                            {{ $value }}
                                        </td>
                                    @endfor
                                @endforeach

                                @if(is_array($scheme->additional_columns) && count($scheme->additional_columns) > 0)
                                    @foreach($scheme->additional_columns as $column)
                                        <td class="px-4 py-3 text-sm">-</td>
                                    @endforeach
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $scheme->sensors->sum(function($s) { return $s->num_of_outputs ?: 1; }) + count($scheme->additional_columns ?? []) + 2 }}" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    No aggregated data available for the selected time range.
                                </td>
                            </tr>
                        @endforelse
                    @else
                        {{-- Display raw data (existing logic) --}}
                        @forelse($paginatedData as $data)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-2 py-3 text-sm">
                                    {{ $data->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                @php $sensorIdx = 0; @endphp
                                @foreach($scheme->sensors as $sensor)
                                    @php
                                                        $outputs = $sensor->num_of_outputs ?: 1;

                                                        // Parse the JSON content
                                                        $jsonData = $data->json_content;
                                                        if (is_string($jsonData)) {
                                                            $jsonData = json_decode($jsonData, true);
                                                        }
                                                        
                                                        // Find sensor data - MODIFIED TO CHECK BOTH ID AND ALIAS
                                                        $pivotAlias = $sensor->pivot->alias ?? null;
                                                        $sensorData = null;
                                                        if (is_array($jsonData)) {
                                                            foreach ($jsonData as $sensorJson) {
                                                                if (
                                                                isset($sensorJson['id']) && $sensorJson['id'] == $sensor->id &&
                                                                (
                                                                (isset($sensorJson['alias']) && $sensorJson['alias'] == $pivotAlias) ||
                                                                (!isset($sensorJson['alias']) && !$pivotAlias)
                                                                )
                                                                ) {
                                                                    $sensorData = $sensorJson;
                                                                    break;
                                                                }
                                                            }
                                                        }

                                                        // Get values array
                                                        $sensorValues = [];
                                                        if ($sensorData && isset($sensorData['values']) && is_array($sensorData['values'])) {
                                                            foreach ($sensorData['values'] as $valueData) {
                                                                $sensorValues[$valueData['label']] = $valueData['value'];
                                                            }
                                                        }

                                                        // Parse output labels
                                                        $outputLabels = [];
                                                        if (isset($sensor->output_labels) && is_string($sensor->output_labels)) {
                                                            $outputLabelsArray = explode(',', $sensor->output_labels);
                                                            foreach ($outputLabelsArray as $i => $label) {
                                                                $outputLabels[] = (object)['position' => $i, 'label' => trim($label)];
                                                            }
                                                        } elseif (method_exists($sensor, 'outputLabels') && is_object($sensor->outputLabels())) {
                                                            $outputLabels = $sensor->outputLabels()->get();
                                                        } elseif (isset($sensorData['values']) && is_array($sensorData['values'])) {
                                                            foreach ($sensorData['values'] as $valueData) {
                                                                $outputLabels[] = (object)['position' => 0, 'label' => $valueData['label']];
                                                            }
                                                        } else {
                                                            for ($i = 0; $i < $outputs; $i++) {
                                                                $outputLabels[] = (object)['position' => $i, 'label' => "Value " . ($i + 1)];
                                                            }
                                                        }
                                    @endphp

                                    @foreach($outputLabels as $i => $outputLabel)
                                        <td class="px-4 py-3 text-sm">
                                            @php
                                                $type = $sensorOutputTypes[$sensorIdx][$i] ?? 'number';
                                                $value = $sensorValues[$outputLabel->label] ?? '-';
                                                if (is_numeric($value)) {
                                                    if ($type === 'percentage') {
                                                        $value = number_format($value * 100, 2) . '%';
                                                    } else {
                                                        $value = number_format($value, 2);
                                                    }
                                                }
                                            @endphp
                                            {{ $value }}
                                        </td>
                                    @endforeach
                                @endforeach

                                @if(is_array($scheme->additional_columns) && count($scheme->additional_columns) > 0)
                                    @foreach($scheme->additional_columns as $column)
                                        <td class="px-4 py-3 text-sm">
                                            @php
                                                // Safely access the additional_content data
                                                $additionalValue = '-';
                                                $additionalData = $data->additional_content;

                                                if (is_array($additionalData) && isset($additionalData[$column['name']])) {
                                                    $additionalValue = $additionalData[$column['name']];

                                                    // Format the value based on data type if needed
                                                    if ($column['data_type'] == 'date' && $additionalValue != '-') {
                                                        $additionalValue = \Carbon\Carbon::parse($additionalValue)->format('Y-m-d');
                                                    } elseif ($column['data_type'] == 'boolean' && $additionalValue != '-') {
                                                        $additionalValue = filter_var($additionalValue, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No';
                                                    }
                                                }
                                            @endphp
                                            {{ $additionalValue }}
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $scheme->sensors->sum(function($s) { return $s->num_of_outputs ?: 1; }) + count($scheme->additional_columns ?? []) + 1 }}" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    No data available for the selected time range.
                                </td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 border-t dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
            {{ $paginatedData->links() }}
        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script>
        let iotChartNumeric;
        let autoRefreshInterval;
        let currentProcessedData = @js($processedData);
        let currentScheme = @json($scheme);
        let lastDataTimestamp = null;
        let isFirstLoad = true;
        let baseAxisConfiguration = null; // Simpan konfigurasi axis dasar
        let currentTimeRange = @js($timeRange); // Add this line
        let currentAggregationType = @js($dataAggregation); // Add this line

        const colors = [
            '#3498db', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6', 
            '#1abc9c', '#f1c40f', '#e67e22', '#34495e', '#7f8c8d',
            '#16a085', '#d35400', '#c0392b', '#8e44ad', '#2980b9'
        ];

        document.addEventListener('livewire:initialized', function() {
            // Initialize last timestamp dari data awal
            if (currentProcessedData && currentProcessedData.length > 0) {
                lastDataTimestamp = currentProcessedData[currentProcessedData.length - 1].created_at;
                console.log('Initial last timestamp:', lastDataTimestamp);
            }

            // Auto refresh functionality
            let autoRefreshInterval = null;
            let lastRefreshTime = Date.now();
            
            // ADD: Function untuk update timestamp UI
            function updateLastUpdatedTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', {
                    hour12: false,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                
                const lastUpdatedElement = document.getElementById('lastUpdated');
                if (lastUpdatedElement) {
                    lastUpdatedElement.textContent = timeString;
                    console.log('🕐 Updated last refresh time:', timeString);
                }
            }
            
            function startAutoRefresh(intervalSeconds = @js($refreshInterval)) {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                }
                
                console.log('🟢 Starting auto refresh with interval:', intervalSeconds, 'seconds');
                
                autoRefreshInterval = setInterval(() => {
                    const now = Date.now();
                    console.log('⏰ Auto refresh triggered at', new Date(now).toLocaleTimeString());
                    
                    // Call Livewire refresh method dengan context auto
                    @this.refreshData('auto');
                    
                    lastRefreshTime = now;
                }, intervalSeconds * 1000);
            }
            
            function stopAutoRefresh() {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                    console.log('🔴 Auto refresh stopped');
                }
            }
            
            // Event listeners untuk auto refresh
            Livewire.on('start-auto-refresh', () => {
                console.log('🟢 Starting auto refresh');
                startAutoRefresh(@js($refreshInterval));
            });
            
            Livewire.on('stop-auto-refresh', () => {
                console.log('🔴 Stopping auto refresh');
                stopAutoRefresh();
            });
            
            Livewire.on('restart-auto-refresh', (event) => {
                console.log('🔄 Restarting auto refresh with new interval');
                let interval = @js($refreshInterval); // Use server-side value as default
                
                // Extract interval from event data
                if (Array.isArray(event) && event[0] && event[0].interval) {
                    interval = event[0].interval;
                } else if (event && event.interval) {
                    interval = event.interval;
                }
                
                stopAutoRefresh();
                startAutoRefresh(interval);
            });
            
            // NEW: Handle delayed restart dengan delay yang lebih lama
            Livewire.on('restart-auto-refresh-delayed', (event) => {
                console.log('🔄 Restarting auto refresh with EXTENDED delay to prevent immediate trigger');
                let interval = @js($refreshInterval);
                
                if (Array.isArray(event) && event[0] && event[0].interval) {
                    interval = event[0].interval;
                } else if (event && event.interval) {
                    interval = event.interval;
                }
                
                stopAutoRefresh();
                
                // EXTENDED delay - 5 seconds untuk memastikan aggregation change selesai
                setTimeout(() => {
                    if (@js($autoRefresh)) { // Only restart if auto refresh is still enabled
                        console.log('🟢 Starting delayed auto refresh after aggregation change');
                        startAutoRefresh(interval);
                    } else {
                        console.log('⏸️ Auto refresh disabled, not restarting');
                    }
                }, 5000); // Increased from 2000 to 5000
            });
            
            // Event listeners untuk data refresh dengan improved logic
            Livewire.on('data-refreshed', (event) => {
                console.log('🔄 Data refresh event received');
                
                let hasNewData = false;
                let context = 'auto';
                let totalDataCount = 0;
                let filteredDataCount = 0;
                
                if (Array.isArray(event) && event.length > 0) {
                    hasNewData = event[0].hasNewData || false;
                    context = event[0].context || 'auto';
                    totalDataCount = event[0].totalDataCount || 0;
                    filteredDataCount = event[0].filteredDataCount || 0;
                } else if (event && typeof event === 'object') {
                    hasNewData = event.hasNewData || false;
                    context = event.context || 'auto';
                    totalDataCount = event.totalDataCount || 0;
                    filteredDataCount = event.filteredDataCount || 0;
                }
                
                // Always update timestamp
                updateLastUpdatedTime();
                
                if (hasNewData) {
                    console.log('✅ New data confirmed by server', { 
                        context: context,
                        totalCount: totalDataCount,
                        filteredCount: filteredDataCount
                    });
                } else {
                    console.log('ℹ️ No new data detected (total count unchanged)', { 
                        context: context,
                        totalCount: totalDataCount,
                        filteredCount: filteredDataCount
                    });
                }
            });
            
            Livewire.on('chart-data-updated', (event) => {
                console.log('=== CHART DATA UPDATE EVENT ===');
                
                let newProcessedData = null;
                let newScheme = null;
                let isTimeRangeChange = false;
                let isAggregationChange = false;
                let newAggregation = currentAggregationType;
                let newTimeRange = currentTimeRange;
                let hasNewData = false;
                let latestTimestamp = null;
                let context = 'auto';
                let totalDataCount = 0;
                let filteredDataCount = 0;
                
                // Handle both array and object formats
                if (Array.isArray(event) && event.length > 0) {
                    const data = event[0];
                    newProcessedData = data.processedData;
                    newScheme = data.scheme;
                    isTimeRangeChange = data.isTimeRangeChange || false;
                    isAggregationChange = data.isAggregationChange || false;
                    newAggregation = data.aggregation || newAggregation;
                    newTimeRange = data.timeRange || newTimeRange;
                    hasNewData = data.hasNewData || false;
                    latestTimestamp = data.latestTimestamp || null;
                    context = data.context || 'auto';
                    totalDataCount = data.totalDataCount || 0;
                    filteredDataCount = data.filteredDataCount || 0;
                } else if (event && typeof event === 'object') {
                    newProcessedData = event.processedData;
                    newScheme = event.scheme;
                    isTimeRangeChange = event.isTimeRangeChange || false;
                    isAggregationChange = event.isAggregationChange || false;
                    newAggregation = event.aggregation || newAggregation;
                    newTimeRange = event.timeRange || newTimeRange;
                    hasNewData = event.hasNewData || false;
                    latestTimestamp = event.latestTimestamp || null;
                    context = event.context || 'auto';
                    totalDataCount = event.totalDataCount || 0;
                    filteredDataCount = event.filteredDataCount || 0;
                }
                
                // Update global variables
                currentAggregationType = newAggregation;
                currentTimeRange = newTimeRange;
                window.currentAggregationType = newAggregation;
                window.currentTimeRange = newTimeRange;
                
                console.log('📊 Chart update details:', {
                    timeRange: currentTimeRange,
                    aggregation: currentAggregationType,
                    isTimeRangeChange: isTimeRangeChange,
                    isAggregationChange: isAggregationChange,
                    hasNewData: hasNewData,
                    context: context,
                    processedDataCount: newProcessedData ? newProcessedData.length : 0,
                    totalRawDataCount: totalDataCount,
                    filteredRawDataCount: filteredDataCount,
                    latestTimestamp: latestTimestamp
                });
                
                // Update data references first
                if (newProcessedData && Array.isArray(newProcessedData)) {
                    currentProcessedData = newProcessedData;
                }
                
                if (newScheme && typeof newScheme === 'object' && newScheme.sensors) {
                    currentScheme = newScheme;
                    console.log('✅ Updated currentScheme with', newScheme.sensors.length, 'sensors');
                }
                
                // Chart update logic tetap sama
                const shouldUpdateChart = isTimeRangeChange || 
                                        isAggregationChange || 
                                        (hasNewData && context === 'auto') || 
                                        context === 'manual';
                
                if (shouldUpdateChart) {
                    setTimeout(() => {
                        if (isTimeRangeChange && !isAggregationChange) {
                            console.log('🔄 Reinitializing chart for time range change...');
                            initChart(false, true); // no animation untuk time range change saja
                        } else if (isAggregationChange) {
                            console.log('🔄 Reinitializing chart for aggregation change with animation...');
                            initChart(true, false); // ANIMATE untuk aggregation change
                        } else if (hasNewData || context === 'manual') {
                            console.log('🔄 Updating chart with new data...', { context: context });
                            initChart(true, true); // animate untuk data baru
                        }
                    }, 200);
                } else {
                    console.log('⏸️ No chart update needed - preserving current state', {
                        context: context,
                        hasNewData: hasNewData,
                        isAggregationChange: isAggregationChange,
                        isTimeRangeChange: isTimeRangeChange,
                        totalCount: totalDataCount,
                        filteredCount: filteredDataCount
                    });
                }
            });
            
            // Start auto refresh jika enabled dari server state
            if (@js($autoRefresh)) {
                console.log('🚀 Auto refresh enabled on page load');
                startAutoRefresh();
            }
            
            // Update timestamp on initial load
            updateLastUpdatedTime();
        });
        
        // Update initChart function dengan parameter preserveAxis
        function initChart(hasNewData = false, preserveAxis = false) {
            // PERBAIKAN LOGIC ANIMASI: 
            // - isFirstLoad: no animation (untuk performa load awal)
            // - isAggregationChange: always animate (untuk visual feedback)
            // - hasNewData: animate (untuk data baru)
            // - time range change tanpa aggregation change: no animation (untuk performa)
            
            const isAggregationChange = !preserveAxis; // preserveAxis false = aggregation change
            const shouldAnimate = !isFirstLoad && (isAggregationChange || hasNewData);
            
            console.log('🎨 Initializing chart...', {
                isFirstLoad: isFirstLoad,
                hasNewData: hasNewData,
                isAggregationChange: isAggregationChange,
                shouldAnimate: shouldAnimate,
                preserveAxis: preserveAxis,
                aggregationType: currentAggregationType,
                timeRange: currentTimeRange
            });
            
            const numericContainer = document.getElementById('iotChartNumeric');

            if (!numericContainer) {
                console.error('❌ Chart container not found!');
                return;
            }

            // Dispose previous chart
            if (iotChartNumeric) {
                try {
                    iotChartNumeric.dispose();
                } catch (e) {
                    console.warn('Warning disposing chart:', e);
                }
                iotChartNumeric = null;
            }

            try {
                const processedData = currentProcessedData;
                const scheme = currentScheme;

                if (!processedData || processedData.length === 0) {
                console.log('ℹ️ No data available');
                // Tampilkan chart kosong dengan axis dan label "No data"
                const emptyChartOptions = {
                    backgroundColor: '#121418',
                    title: {
                        text: 'No data available for selected time range',
                        left: 'center',
                        top: 'middle',
                        textStyle: {
                            color: '#f8fafc',
                            fontSize: 16,
                            fontWeight: 'normal'
                        }
                    },
                    xAxis: {
                        type: 'category',
                        data: [],
                        axisLabel: { color: '#f8fafc' },
                        axisLine: { lineStyle: { color: 'rgba(255,255,255,0.3)' } }
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: { color: '#f8fafc' },
                        axisLine: { lineStyle: { color: 'rgba(255,255,255,0.3)' } }
                    },
                    series: []
                };
                if (!iotChartNumeric) {
                    iotChartNumeric = echarts.init(numericContainer);
                }
                iotChartNumeric.clear();
                iotChartNumeric.setOption(emptyChartOptions, true);
                return;
            }

                console.log('📊 Processing', processedData.length, 'data points for aggregation:', currentAggregationType, 'time range:', currentTimeRange);

                // Format dates based on current aggregation type and time range
                const formattedDates = processedData.map(item => {
                    const date = new Date(item.created_at);
                    
                    // For 24h range, always show detailed time format regardless of aggregation
                    if (currentTimeRange === '24h' && currentAggregationType === 'raw') {
                        return date.toLocaleDateString('en-CA') + ' ' + 
                               date.toLocaleTimeString('en-US', {
                                   hour: '2-digit',
                                   minute: '2-digit',
                                   second: '2-digit',
                                   hour12: false
                               });
                    }
                    
                    switch(currentAggregationType) {
                        case 'daily':
                            return date.toLocaleDateString('en-CA'); // YYYY-MM-DD format
                            
                        case 'weekly':
                            const weekOfMonth = Math.ceil(date.getDate() / 7);
                            const monthYear = date.toLocaleDateString('en-US', { month: '2-digit', year: '2-digit' });
                            return `Week ${weekOfMonth} ${monthYear}`;
                            
                        case 'monthly':
                            return date.toLocaleDateString('en-US', { month: '2-digit', year: 'numeric' }); // MM/YYYY
                            
                        case 'quarterly':
                            const quarter = Math.ceil((date.getMonth() + 1) / 3);
                            return `Q${quarter} ${date.getFullYear()}`;
                            
                        case 'hourly':
                            return date.toLocaleDateString('en-CA') + ' ' + 
                                   date.toLocaleTimeString('en-US', {
                                       hour: '2-digit',
                                       minute: '2-digit',
                                       hour12: false
                                   });
                            
                        default: // raw data
                            return date.toLocaleDateString('en-CA') + ' ' + 
                                   date.toLocaleTimeString('en-US', {
                                       hour: '2-digit',
                                       minute: '2-digit',
                                       second: '2-digit',
                                       hour12: false
                                   });
                    }
                });

                const maxDataPoints = 15;
                const totalPoints = formattedDates.length;
                
                const windowPercent = totalPoints > 0 ? (maxDataPoints / totalPoints) * 100 : 100;
                const startPercent = totalPoints > maxDataPoints ? 
                    Math.max(0, 100 - windowPercent) : 0;
                const endPercent = 100;

                // Base chart options dengan enhanced animation configuration
                const baseOptions = {
                    backgroundColor: '#121418',
                    animation: shouldAnimate,
                    animationDuration: shouldAnimate ? (isAggregationChange ? 1000 : 800) : 0,
                    animationEasing: shouldAnimate ? (isAggregationChange ? 'elasticOut' : 'cubicOut') : 'linear',
                    animationDelay: function (idx) {
                        // Staggered animation untuk series yang berbeda
                        return shouldAnimate ? idx * (isAggregationChange ? 150 : 100) : 0;
                    },
                    // TAMBAHAN: Animasi untuk data point individual
                    animationDurationUpdate: shouldAnimate ? 600 : 0,
                    animationEasingUpdate: shouldAnimate ? 'cubicInOut' : 'linear',
                    animationDelayUpdate: function (idx) {
                        return shouldAnimate ? idx * (isAggregationChange ? 50 : 30) : 0;
                    },
                    grid: {
                        left: '5%',
                        right: '5%',
                        bottom: '22.5%',
                        top: '17.5%',
                        containLabel: true,
                        borderColor: '#0f172a',
                        borderWidth: 1,
                        shadowColor: 'rgba(0, 0, 0, 0.3)', 
                        shadowBlur: 10
                    },
                    xAxis: {
                        type: 'category',
                        data: formattedDates,
                        axisLabel: {
                            color: '#f8fafc',
                            interval: 0,
                            rotate: currentAggregationType === 'raw' || currentAggregationType === 'hourly' ? 45 : 0,
                            fontSize: currentAggregationType === 'quarterly' || currentAggregationType === 'monthly' ? 10 : 9,
                            margin: 8,
                            formatter: function(value) {
                                // For aggregated data, show the full formatted value
                                if (currentAggregationType !== 'raw' && currentAggregationType !== 'hourly') {
                                    return value;
                                }
                                
                                // For raw and hourly data, show only time part if space is limited
                                const parts = value.split(' ');
                                if (parts.length > 1) {
                                    return parts[1]; // Show time part
                                }
                                return value;
                            }
                        },
                        axisLine: {
                            lineStyle: {
                                color: 'rgba(255, 255, 255, 0.3)'
                            }
                        },
                        axisTick: {
                            alignWithLabel: true,
                            length: 4,
                            lineStyle: {
                                color: 'rgba(255, 255, 255, 0.3)'
                            }
                        }
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: { color: '#f8fafc' },
                        axisLine: { lineStyle: { color: 'rgba(255, 255, 255, 0.3)' } },
                        axisTick: { lineStyle: { color: 'rgba(255, 255, 255, 0.3)' } },
                        splitLine: { lineStyle: { color: 'rgba(255, 255, 255, 0.1)' } }
                    },
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor: 'rgba(40, 40, 50, 0.8)',
                        borderColor: '#555',
                        borderWidth: 1,
                        textStyle: { 
                            color: '#f8fafc',
                            fontSize: 12 
                        },
                        formatter: function(params) {
                            const dataIndex = params[0].dataIndex;
                            const originalData = processedData[dataIndex];
                            const date = new Date(originalData.created_at);
                            
                            let dateLabel;
                            switch(currentAggregationType) {
                                case 'daily':
                                    dateLabel = date.toLocaleDateString('en-US', { 
                                        weekday: 'short',
                                        year: 'numeric', 
                                        month: 'short', 
                                        day: 'numeric' 
                                    });
                                    break;
                                case 'weekly':
                                    const weekOfMonth = Math.ceil(date.getDate() / 7);
                                    dateLabel = `Week ${weekOfMonth} - ${date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}`;
                                    break;
                                case 'monthly':
                                    dateLabel = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                                    break;
                                case 'quarterly':
                                    const quarter = Math.ceil((date.getMonth() + 1) / 3);
                                    const quarterMonths = ['Jan-Mar', 'Apr-Jun', 'Jul-Sep', 'Oct-Dec'];
                                    dateLabel = `Q${quarter} ${date.getFullYear()} (${quarterMonths[quarter-1]})`;
                                    break;
                                case 'hourly':
                                    dateLabel = date.toLocaleDateString('en-US', { 
                                        month: 'short', 
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        hour12: false
                                    });
                                    break;
                                default:
                                    dateLabel = date.toLocaleDateString('en-US', { 
                                        month: 'short', 
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        second: '2-digit',
                                        hour12: false
                                    });
                            }
                            
                            let tooltipContent = `<div style="margin-bottom: 5px;"><strong>${dateLabel}</strong></div>`;
                            
                            // Add aggregation info for non-raw data
                            if (currentAggregationType !== 'raw' && originalData.data_count) {
                                tooltipContent += `<div style="margin-bottom: 5px; font-size: 10px; color: #ccc;">
                                    ${originalData.data_count} data points aggregated
                                </div>`;
                            }
                            
                            params.forEach(function(param) {
                                const value = param.value !== null ? param.value.toFixed(2) : 'N/A';
                                tooltipContent += `<div style="margin-bottom: 2px;">
                                    <span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:${param.color};"></span>
                                    ${param.seriesName}: <strong>${value}</strong>
                                </div>`;
                            });
                            
                            return tooltipContent;
                        },
                        axisPointer: {
                            type: 'cross',
                            lineStyle: {
                                color: '#888',
                                type: 'dashed'
                            },
                            crossStyle: {
                                color: '#888',
                                type: 'dashed'
                            },
                            label: {
                                backgroundColor: '#6a7985'
                            }
                        }
                    },
                    legend: {
                        show: true,
                        type: 'scroll',
                        orient: 'horizontal',
                        top: '8%',
                        left: 'center',
                        textStyle: {
                            color: '#f8fafc'
                        },
                        pageTextStyle: { color: '#f8fafc' },
                        pageIconColor: '#aaa',
                        pageIconInactiveColor: '#555'
                    },
                    dataZoom: [
                        {
                            type: 'slider',
                            show: true,
                            xAxisIndex: [0],
                            yAxisIndex: [], 
                            height: 20,
                            bottom: '15%',
                            start: startPercent, 
                            end: endPercent,
                            zoomOnMouseWheel: false,
                            moveOnMouseWheel: true,
                            zoomLock: true,         
                            brushSelect: false,     
                            throttle: 100, 
                            rangeMode: ['value', 'value'] 
                        },
                        {
                            type: 'inside',
                            xAxisIndex: [0],
                            yAxisIndex: [], 
                            zoomOnMouseWheel: false,
                            moveOnMouseWheel: true,
                            zoomLock: true,
                            throttle: 100
                        }
                    ]
                };

                let allSeries = [];
                processSensorData(scheme, processedData, allSeries, shouldAnimate);
                console.log(`Generated ${allSeries.length} total series with animation:`, shouldAnimate);

                // Handle axis configuration berdasarkan preserveAxis flag
                let seriesGroups;
                if (preserveAxis && baseAxisConfiguration) {
                    // Gunakan konfigurasi axis yang sudah ada, update hanya range data
                    console.log('🔧 Using preserved axis configuration');
                    seriesGroups = JSON.parse(JSON.stringify(baseAxisConfiguration));
                    updateAxisRangesForTimeRange(seriesGroups, allSeries);
                } else {
                    // Buat konfigurasi axis baru
                    console.log('🔧 Creating new axis configuration');
                    seriesGroups = analyzeNumericSeriesRanges(allSeries);
                    
                    // Simpan sebagai base configuration untuk time range changes selanjutnya
                    if (isFirstLoad || !baseAxisConfiguration) {
                        baseAxisConfiguration = JSON.parse(JSON.stringify(seriesGroups));
                        console.log('💾 Saved base axis configuration');
                    }
                }

                if (allSeries.length > 0 && numericContainer) {
                    const chartOptions = JSON.parse(JSON.stringify(baseOptions));
                    setupMultiAxisOptions(chartOptions, allSeries, seriesGroups, startPercent, endPercent);
                    
                    chartOptions.series = allSeries;
                    
                    try {
                        console.log('Creating chart...', {
                            animationEnabled: shouldAnimate,
                            animationDuration: shouldAnimate ? (isAggregationChange ? 1000 : 800) : 0,
                            animationType: isAggregationChange ? 'aggregation' : 'data',
                            dataLength: processedData.length,
                            axisCount: Object.keys(seriesGroups).length
                        });
                        
                        iotChartNumeric = echarts.init(numericContainer);
                        iotChartNumeric.setOption(chartOptions, true);
                        
                        console.log('✅ Chart successfully updated with animation:', shouldAnimate, 
                                  isAggregationChange ? '(aggregation change)' : '(data update)');
                        
                        // Set first load ke false setelah chart pertama kali dibuat
                        if (isFirstLoad) {
                            isFirstLoad = false;
                            console.log('🎯 First load completed');
                        }
                        
                    } catch (e) {
                        console.error('Error initializing chart:', e);
                        numericContainer.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">Error initializing chart</div>';
                    }
                } else if (numericContainer) {
                    numericContainer.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">No data available</div>';
                }

                window.addEventListener('resize', function() {
                    if (iotChartNumeric) iotChartNumeric.resize();
                });

            } catch (error) {
                console.error('❌ Error in chart initialization:', error);
                if (numericContainer) {
                    numericContainer.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">Error initializing chart</div>';
                }
            }
        }

        // Function untuk update axis ranges saat time range berubah
        function updateAxisRangesForTimeRange(preservedConfig, allSeries) {
            console.log('🔄 Updating axis ranges for time range change');
            
            Object.keys(preservedConfig).forEach(axisIndex => {
                const axisGroup = preservedConfig[axisIndex];
                
                if (axisGroup.isPercentage) {
                    // Percentage axis tetap 0-1
                    return;
                }
                
                let groupMin = Infinity;
                let groupMax = -Infinity;
                
                // Ambil data terbaru dari series yang ada
                axisGroup.series.forEach(seriesInfo => {
                    const series = allSeries[seriesInfo.index];
                    if (series && series.data) {
                        const validValues = series.data.filter(v => v !== null && v !== undefined && !isNaN(v));
                        if (validValues.length > 0) {
                            const seriesMin = Math.min(...validValues);
                            const seriesMax = Math.max(...validValues);
                            groupMin = Math.min(groupMin, seriesMin);
                            groupMax = Math.max(groupMax, seriesMax);
                        }
                    }
                });
                
                if (isFinite(groupMin) && isFinite(groupMax)) {
                    const range = groupMax - groupMin;
                    const buffer = range === 0 ? 1 : range * 0.15;
                    
                    // Update range berdasarkan data terbaru, tapi tetap gunakan validation jika ada
                    if (axisGroup.source === 'database') {
                        // Untuk database range, gunakan validation settings tapi sesuaikan jika data melebihi
                        const originalMin = axisGroup.axisMin;
                        const originalMax = axisGroup.axisMax;
                        
                        axisGroup.axisMin = Math.min(originalMin, groupMin - buffer);
                        axisGroup.axisMax = Math.max(originalMax, groupMax + buffer);
                    } else {
                        // Untuk calculated range, update sepenuhnya
                        axisGroup.axisMin = groupMin - buffer;
                        axisGroup.axisMax = groupMax + buffer;
                    }
                    
                    console.log(`Updated axis ${axisIndex} range: ${axisGroup.axisMin.toFixed(2)} - ${axisGroup.axisMax.toFixed(2)}`);
                }
            });
        }

        function processSensorData(scheme, processedData, allSeries, shouldAnimate = false, isAggregationChange = false) {
            let colorIndex = 0;

            scheme.sensors.forEach(sensor => {
                // Validation settings
                let validationSettings = {};
                if (sensor.validation_settings) {
                    try {
                        validationSettings = typeof sensor.validation_settings === 'string'
                            ? JSON.parse(sensor.validation_settings)
                            : sensor.validation_settings;
                    } catch (e) { 
                        console.warn('Error parsing validation settings for sensor', sensor.id, e);
                    }
                }

                // Output labels
                let outputLabels = [];
                const numOutputs = sensor.num_of_outputs || 1;
                const labelsString = sensor.output_labels || '';
                if (labelsString && typeof labelsString === 'string') {
                    const labelsArray = labelsString.split(',').map(s => s.trim());
                    for (let i = 0; i < numOutputs; i++) {
                        outputLabels.push(labelsArray[i] || `Value ${i + 1}`);
                    }
                } else {
                    for (let i = 0; i < numOutputs; i++) {
                        outputLabels.push(`Value ${i + 1}`);
                    }
                }

                outputLabels.forEach((label, index) => {
                    let outputValidation = null;
                    if (Array.isArray(validationSettings)) {
                        outputValidation = validationSettings[index];
                    } else if (validationSettings && typeof validationSettings === 'object') {
                        outputValidation = validationSettings;
                    }

                    const dataPoints = processedData.map(item => {
                        let value = null;
                        if (Array.isArray(item.sensors)) {
                            let sensorData = null;
                            if (sensor.pivot && sensor.pivot.alias) {
                                sensorData = item.sensors.find(s =>
                                    String(s.id) == String(sensor.id) && String(s.alias || '') == String(sensor.pivot.alias || '')
                                );
                            }
                            if (!sensorData) {
                                sensorData = item.sensors.find(s => String(s.id) == String(sensor.id));
                            }
                            if (sensorData && sensorData.values && typeof sensorData.values === 'object') {
                                if (sensorData.values[label] !== undefined && sensorData.values[label] !== null) {
                                    value = parseFloat(sensorData.values[label]);
                                    if (isNaN(value)) value = null;
                                }
                            }
                        }
                        return value;
                    });

                    const sensorName = sensor.pivot?.alias || sensor.name;
                    const seriesName = `${sensorName} - ${label}`;

                    const isPercentage = outputValidation?.type?.toLowerCase() === 'percentage';
                    const color = colors[colorIndex % colors.length];

                    // ENHANCED ANIMATION CONFIG DENGAN DIFFERENT TYPES
                    const animationConfig = {
                        animation: shouldAnimate,
                        animationDuration: shouldAnimate ? (isAggregationChange ? 1000 : 800) : 0,
                        animationEasing: shouldAnimate ? (isAggregationChange ? 'elasticOut' : 'cubicOut') : 'linear',
                        animationDelay: shouldAnimate ? colorIndex * (isAggregationChange ? 200 : 100) : 0,
                        // Per-point animation dengan timing yang berbeda
                        animationDurationUpdate: shouldAnimate ? (isAggregationChange ? 600 : 400) : 0,
                        animationEasingUpdate: shouldAnimate ? 'cubicInOut' : 'linear',
                        animationDelayUpdate: function (idx) {
                            if (!shouldAnimate) return 0;
                            return isAggregationChange ? idx * 80 : idx * 40; // Lebih lambat untuk aggregation change
                        }
                    };

                    const seriesConfig = {
                        name: seriesName,
                        type: 'line',
                        data: dataPoints,
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 6,
                        itemStyle: { color },
                        lineStyle: { width: 2, color },
                        areaStyle: { opacity: 0.2, color },
                        // Apply animation config
                        ...animationConfig,
                        sensorInfo: {
                            id: sensor.id,
                            alias: sensor.pivot?.alias,
                            isPercentage: isPercentage,
                            validationMin: outputValidation?.min,
                            validationMax: outputValidation?.max,
                            validationType: outputValidation?.type
                        }
                    };

                    allSeries.push(seriesConfig);
                    colorIndex++;
                });
            });
        }

        function getAppropriateAxisRange(unit, label, min, max, sensorInfo) {
            if (sensorInfo && 
                sensorInfo.validationMin !== undefined && sensorInfo.validationMin !== null &&
                sensorInfo.validationMax !== undefined && sensorInfo.validationMax !== null) {
                
                const dbMin = parseFloat(sensorInfo.validationMin);
                const dbMax = parseFloat(sensorInfo.validationMax);
                
                if (!isNaN(dbMin) && !isNaN(dbMax) && isFinite(dbMin) && isFinite(dbMax) && dbMin < dbMax) {
                    const range = dbMax - dbMin;
                    const buffer = range === 0 ? 0.5 : range * 0.05; 
                    
                    return {
                        min: dbMin - buffer,
                        max: dbMax + buffer,
                        source: 'database' 
                    };
                }
            }
            
            const calculatedMin = min;
            const calculatedMax = max;
            const range = calculatedMax - calculatedMin;
            
            if (!isFinite(calculatedMin) || !isFinite(calculatedMax) || range === 0 || range < 0.0001) {
                const value = isFinite(calculatedMin) ? calculatedMin : 0; 
                const artificialRange = Math.max(1, Math.abs(value) * 0.2); 
                return {
                    min: value - artificialRange,
                    max: value + artificialRange,
                    source: 'calculated_fallback' 
                };
            }
            
            const buffer = range * 0.15; 
            
            return {
                min: calculatedMin - buffer,
                max: calculatedMax + buffer,
                source: 'calculated' 
            };
        }

        function analyzeNumericSeriesRanges(allSeries) {
            if (!allSeries || allSeries.length === 0) {
                return {};
            }

            try {
                const seriesInfo = allSeries.map((s, index) => {
                    const validValues = s.data.filter(v => v !== null && v !== undefined && !isNaN(v));
                    const calcMin = validValues.length > 0 ? Math.min(...validValues) : 0;
                    const calcMax = validValues.length > 0 ? Math.max(...validValues) : (calcMin + 1);

                    const isPercentage = s.sensorInfo?.isPercentage || false;

                    const rangeInfo = !isPercentage ? getAppropriateAxisRange(
                        '',
                        s.name,
                        calcMin,
                        calcMax,
                        s.sensorInfo    
                    ) : { min: 0, max: 1, source: 'percentage' };

                    return {
                        index,
                        name: s.name,
                        unit: '',
                        isPercentage: isPercentage,
                        dbMin: !isPercentage ? ((rangeInfo.source === 'database' && s.sensorInfo?.validationMin !== undefined) ? parseFloat(s.sensorInfo.validationMin) : undefined) : undefined,
                        dbMax: !isPercentage ? ((rangeInfo.source === 'database' && s.sensorInfo?.validationMax !== undefined) ? parseFloat(s.sensorInfo.validationMax) : undefined) : undefined,
                        axisMin: rangeInfo.min,
                        axisMax: rangeInfo.max,
                        rangeSource: rangeInfo.source
                    };
                });

                const percentageInfo = seriesInfo.filter(info => info.isPercentage);
                const numericInfo = seriesInfo.filter(info => !info.isPercentage);

                const finalGroups = {};
                let axisIndex = 0;

                if (percentageInfo.length > 0) {
                    finalGroups[axisIndex] = {
                        series: percentageInfo,
                        unit: '%',
                        axisMin: 0,
                        axisMax: 1,
                        source: 'percentage',
                        isPercentage: true
                    };
                    axisIndex++;
                }

                const unitGroups = {};
                numericInfo.forEach(info => {
                    const unit = info.unit;
                    if (!unitGroups[unit]) unitGroups[unit] = [];
                    unitGroups[unit].push(info);
                });

                for (const unit in unitGroups) {
                    const currentUnitGroup = unitGroups[unit];
                    const dbRangedSeries = currentUnitGroup.filter(s => s.rangeSource === 'database');
                    const calcRangedSeries = currentUnitGroup.filter(s => s.rangeSource !== 'database');

                    if (dbRangedSeries.length > 0) {
                        const uniqueDbRanges = {};
                        dbRangedSeries.forEach(s => {
                            if (s.dbMin !== undefined && s.dbMax !== undefined) {
                                const rangeKey = `${s.dbMin}-${s.dbMax}`;
                                if (!uniqueDbRanges[rangeKey]) {
                                    uniqueDbRanges[rangeKey] = { series: [], axisMin: s.axisMin, axisMax: s.axisMax };
                                }
                                uniqueDbRanges[rangeKey].series.push(s);
                            } else {
                                calcRangedSeries.push(s);
                            }
                        });

                        for (const rangeKey in uniqueDbRanges) {
                            const rangeGroup = uniqueDbRanges[rangeKey];
                            finalGroups[axisIndex] = {
                                series: rangeGroup.series,
                                unit: unit,
                                axisMin: rangeGroup.axisMin,
                                axisMax: rangeGroup.axisMax,
                                source: 'database',
                                isPercentage: false
                            };
                            axisIndex++;
                        }
                    }

                    if (calcRangedSeries.length > 0) {
                        let groupMin = Infinity;
                        let groupMax = -Infinity;
                        calcRangedSeries.forEach(s => {
                            groupMin = Math.min(groupMin, s.axisMin);
                            groupMax = Math.max(groupMax, s.axisMax);
                        });

                        finalGroups[axisIndex] = {
                            series: calcRangedSeries,
                            unit: unit,
                            axisMin: isFinite(groupMin) ? groupMin : 0,
                            axisMax: isFinite(groupMax) ? groupMax : 100,
                            source: 'calculated',
                            isPercentage: false
                        };
                        axisIndex++;
                    }
                }

                return finalGroups;
            } catch (err) {
                console.error('Error analyzing combined series ranges:', err);
                return {};
            }
        }

        function setupMultiAxisOptions(options, allSeries, finalGroups, startPercent = 0, endPercent = 100) {
            if (!finalGroups || Object.keys(finalGroups).length === 0) {
                console.warn('No final groups provided for multi-axis setup');
                return;
            }

            const axisColors = ['#3498db', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6', '#1abc9c'];
            options.yAxis = [];

            Object.keys(finalGroups).forEach((groupIndexStr) => {
                const groupIndex = parseInt(groupIndexStr);
                const groupData = finalGroups[groupIndex];
                const position = groupIndex % 2 === 0 ? 'left' : 'right';
                const offset = Math.floor(groupIndex / 2) * 60;

                let axisConfig = {
                    type: 'value',
                    position: position,
                    offset: offset,
                    name: groupData.unit || '',
                    min: groupData.axisMin,
                    max: groupData.axisMax,
                    axisLine: {
                        show: true,
                        lineStyle: { color: axisColors[groupIndex % axisColors.length] }
                    },
                    axisLabel: {
                        color: '#f8fafc',
                        formatter: function(value) {
                            return value.toFixed(Math.abs(value) < 10 ? 1 : 0);
                        }
                    },
                    splitLine: {
                        show: groupIndex === 0,
                        lineStyle: { color: 'rgba(255, 255, 255, 0.1)', type: 'dashed' }
                    }
                };

                if (groupData.isPercentage) {
                    axisConfig.name = '%';
                    axisConfig.min = 0;
                    axisConfig.max = 1;
                    axisConfig.axisLabel.formatter = function(value) {
                        return (value * 100).toFixed(0) + '%';
                    };
                }

                options.yAxis.push(axisConfig);

                groupData.series.forEach(seriesInfo => {
                    if (seriesInfo.index >= 0 && seriesInfo.index < allSeries.length) {
                        allSeries[seriesInfo.index].yAxisIndex = groupIndex;
                    }
                });
            });

            const rightAxisCount = options.yAxis.filter((axis, index) => index % 2 !== 0).length;
            options.grid.right = `${5 + (rightAxisCount > 0 ? (rightAxisCount - 1) * 4 : 0)}%`;
            const leftAxesCount = options.yAxis.filter((axis, index) => index % 2 === 0).length;
            options.grid.left = `${5 + (leftAxesCount > 0 ? (leftAxesCount - 1) * 4 : 0)}%`;
        }

        // Initial chart load
        document.addEventListener('DOMContentLoaded', function() {
            isFirstLoad = true;
            setTimeout(() => {
                console.log('🎬 Initial chart load');
                initChart(false);
            }, 500);
        });
    </script>
    @endpush
    
    <style>
        /* Make chart container fill available height */
        #iotChartNumeric {
            width: 100%;
            height: calc(100% - 60px);
            min-height: 300px; /* Ensure minimum height */
            border-radius: 8px;
        }
        
        #iotChartNumeric canvas {
            border-radius: 8px;
        }

        .chart-container {
            display: flex;
            flex-direction: column;
        }
        
        .chart-controls { 
            flex: 0 0 auto;
        }
        
        .chart-area {
            flex: 1 1 auto;
            min-height: 400px; /* Ensure minimum height */
            overflow: hidden;
        }
    </style>
</div>

