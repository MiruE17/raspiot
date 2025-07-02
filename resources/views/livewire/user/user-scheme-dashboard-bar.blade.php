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
                <th class="px-2 py-3">Select</th>
                @if($dataAggregation === 'raw' || $showRawData)
                <th class="px-2 py-3">Time</th>
                @endif
                
                @if($dataAggregation !== 'raw' && !$showRawData)
                <th class="px-4 py-3">Period</th>
                <th class="px-4 py-3">Data Points</th>
                @endif
                
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
        
        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
            @php
            // Siapkan array tipe output untuk setiap sensor
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
            
            @if($dataAggregation !== 'raw' && !$showRawData)
            {{-- Display aggregated data --}}
            @forelse($paginatedData as $idx => $data)
            @php
            $globalIdx = array_search($data['id'], array_column($processedData, 'id'));
            @endphp
            <tr class="text-gray-700 dark:text-gray-400 {{ ($selectedBarRecord === $globalIdx && ($dataAggregation !== 'raw' && !$showRawData)) ? 'bg-blue-50 dark:bg-blue-900' : '' }}">
                <td class="px-2 py-3">
                    <button 
                    wire:click="selectBarRecordById('{{ $data['id'] }}')"
                    class="px-2 py-1 text-xs rounded {{ ($selectedBarRecord === $globalIdx && ($dataAggregation !== 'raw' && !$showRawData)) ? 'bg-blue-600 text-white ring-2 ring-blue-400' : 'bg-gray-50 text-gray-700 hover:bg-blue-100' }}"
                    title="Select this record for bar chart"
                    >
                    {{ ($selectedBarRecord === $globalIdx && ($dataAggregation !== 'raw' && !$showRawData)) ? 'Selected' : 'Select' }}
                </button>
            </td>
            <td class="px-4 py-3 text-sm font-medium">
                {{ $data['period'] }}
            </td>
            <td class="px-4 py-3 text-sm">
                {{ $data['data_count'] }} records
            </td>
            @php $sensorIdx = 0; @endphp
            @foreach($scheme->sensors as $sensor)
            @php
            $outputs = $sensor->num_of_outputs ?: 1;
            $outputLabels = explode(',', $sensor->output_labels ?? '');
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
            $value = $sensorData && isset($sensorData['values'][$label])
            ? $sensorData['values'][$label]
            : '-';
            $type = $sensorOutputTypes[$sensorIdx][$i] ?? 'number';
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
            @php $sensorIdx++; @endphp
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
        {{-- Display raw data --}}
        @forelse($paginatedData as $idx => $data)
        @php
        $globalIdx = array_search($data->id, array_column($processedData, 'id'));
        @endphp
        <tr class="text-gray-700 dark:text-gray-400 {{ ($selectedBarRecord === $globalIdx && ($dataAggregation === 'raw' && $showRawData)) ? 'bg-blue-50 dark:bg-blue-900' : '' }}">
            <td class="px-2 py-3">
                <button 
                wire:click="selectBarRecordById('{{ $data->id }}')"
                class="px-2 py-1 text-xs rounded {{ ($selectedBarRecord === $globalIdx && ($dataAggregation === 'raw' && $showRawData)) ? 'bg-blue-600 text-white ring-2 ring-blue-400' : 'bg-orange-100 text-gray-700 hover:bg-blue-100' }}"
                title="Select this record for bar chart"
                >
                {{ ($selectedBarRecord === $globalIdx && ($dataAggregation === 'raw' && $showRawData)) ? 'Selected' : 'Select' }}
            </button>
        </td>
        <td class="px-2 py-3 text-sm">
            {{ $data->created_at->format('Y-m-d H:i:s') }}
        </td>
        @php $sensorIdx = 0; @endphp
        @foreach($scheme->sensors as $sensor)
        @php
        $outputs = $sensor->num_of_outputs ?: 1;
        $jsonData = $data->json_content;
        if (is_string($jsonData)) {
            $jsonData = json_decode($jsonData, true);
        }
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
                // Support both array and associative array
                if (is_array($valueData) && isset($valueData['label'], $valueData['value'])) {
                    $sensorValues[$valueData['label']] = $valueData['value'];
                } elseif (is_string($valueData)) {
                    $sensorValues[$valueData] = $valueData;
                }
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
        @php
        $value = $sensorValues[$outputLabel->label] ?? '-';
        $type = $sensorOutputTypes[$sensorIdx][$i] ?? 'number';
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
        @endforeach
        @php $sensorIdx++; @endphp
        @endforeach
        
        @if(is_array($scheme->additional_columns) && count($scheme->additional_columns) > 0)
        @foreach($scheme->additional_columns as $column)
        <td class="px-4 py-3 text-sm">
            @php
            $additionalValue = '-';
            $additionalData = $data->additional_content;
            if (is_array($additionalData) && isset($additionalData[$column['name']])) {
                $additionalValue = $additionalData[$column['name']];
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
    let selectedBarRecord = null;
    
    Livewire.on('bar-record-selected', (idx) => {
        selectedBarRecord = idx;
        renderBarChart(currentProcessedData, currentScheme, selectedBarRecord);
    });
    
    function renderBarChart(processedData, scheme, selectedIdx = null){
        // Ambil validation_settings dari setiap sensor
        let sensorOutputTypes = [];
        scheme.sensors.forEach(sensor => {
            let types = [];
            let validation = [];
            try {
                validation = sensor.validation_settings ? JSON.parse(sensor.validation_settings) : [];
            } catch (e) {
                validation = [];
            }
            for (let i = 0; i < (sensor.num_of_outputs || 1); i++) {
                types.push(validation[i]?.type || 'number');
            }
            sensorOutputTypes.push(types);
        });
        
        if (!processedData || processedData.length === 0) {
            const chart = echarts.init(document.getElementById('iotChartNumeric'));
            chart.clear();
            chart.setOption({
                title: {
                    text: 'No data available',
                    left: 'center',
                    top: 'middle',
                    textStyle: { color: '#f8fafc', fontSize: 16 }
                }
            });
            return;
        }
        
        // Ambil record terakhir
        let latest;
        if (selectedIdx !== null && processedData[selectedIdx]) {
            latest = processedData[selectedIdx];
        } else {
            latest = processedData[processedData.length - 1];
        }
        
        // Siapkan label, value, dan format untuk setiap sensor-output
        let barLabels = [];
        let barValues = [];
        let barFormats = [];
        let colors = [
        '#3498db', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6', 
        '#1abc9c', '#f1c40f', '#e67e22', '#34495e', '#7f8c8d'
        ];
        
        let sensorIdx = 0;
        scheme.sensors.forEach(sensor => {
            const outputs = sensor.num_of_outputs || 1;
            let outputLabels = [];
            if (sensor.output_labels) {
                outputLabels = sensor.output_labels.split(',').map(s => s.trim());
            }
            // Cari data sensor pada latest.sensors hanya berdasarkan id
            let sensorData = null;
            if (Array.isArray(latest.sensors)) {
                sensorData = latest.sensors.find(s => String(s.id) === String(sensor.id));
            }
            for (let i = 0; i < outputs; i++) {
                let label = (outputLabels[i] || sensor.name) + (outputs > 1 ? ` (${outputLabels[i] || 'Output ' + (i+1)})` : '');
                let value = '-';
                let type = (sensorOutputTypes[sensorIdx] && sensorOutputTypes[sensorIdx][i]) ? sensorOutputTypes[sensorIdx][i] : 'number';
                if (sensorData && sensorData.values && typeof sensorData.values === 'object') {
                    const val = sensorData.values[outputLabels[i] || `Value ${i+1}`];
                    value = (val !== undefined && val !== null && !isNaN(val)) ? parseFloat(val) : '-';
                }
                barLabels.push(label);
                barValues.push(value);
                barFormats.push(type);
            }
            sensorIdx++;
        });
        
        // Cek jika ada tipe percentage
        let hasPercentage = barFormats.includes('percentage');
        
        // Inisialisasi bar chart
        const chart = echarts.init(document.getElementById('iotChartNumeric'));
        chart.clear();
        chart.setOption({
            backgroundColor: '#121418',
            grid: {
                left: '5%',
                right: '5%',
                top: 60,
                bottom: 60,
                containLabel: true
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'shadow' },
                formatter: function(params) {
                    let html = '';
                    params.forEach(function(item, idx) {
                        let val = item.value;
                        if (barFormats[item.dataIndex] === 'percentage' && val !== '-') {
                            val = (parseFloat(val) * 100).toFixed(2) + '%';
                        }
                        html += `<div><span style="display:inline-block;margin-right:5px;width:10px;height:10px;background:${item.color};"></span>${item.name}: <b>${val}</b></div>`;
                    });
                    return html;
                }
            },
            xAxis: {
                type: 'category',
                data: barLabels,
                axisLabel: { color: '#f8fafc', rotate: 20 }
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    color: '#f8fafc',
                    formatter: function(value, idx) {
                        // Jika semua output percentage, tampilkan %
                        if (hasPercentage) return (value * 100).toFixed(0) + '%';
                        return value;
                    }
                },
                splitLine: { lineStyle: { color: 'rgba(255,255,255,0.1)' } },
                min: 0,
                max: function(value) {
                    // Jika ada percentage, pastikan max 1 (100%)
                    //if (hasPercentage) return 1;
                    return value.max;
                }
            },
            series: [{
                name: 'Value',
                type: 'bar',
                data: barValues.map((v, i) => {
                    if (barFormats[i] === 'percentage' && v !== '-') {
                        return parseFloat(v);
                    }
                    return v;
                }),
                itemStyle: {
                    color: function(params) {
                        return colors[params.dataIndex % colors.length];
                    }
                },
                label: {
                    show: true,
                    position: 'top',
                    color: '#f8fafc',
                    formatter: function(params) {
                        let val = params.value;
                        if (barFormats[params.dataIndex] === 'percentage' && val !== '-') {
                            return (parseFloat(val) * 100).toFixed(2) + '%';
                        }
                        return val !== '-' ? val : '';
                    }
                },
                barMaxWidth: 40
            }]
        });
        window.addEventListener('resize', function() {
            chart.resize();
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        renderBarChart(@json($processedData), @json($scheme));
    });
    let autoRefreshInterval;
    let lastBarDataTimestamp = null;
    let currentProcessedData = @js($processedData);
    let currentScheme = @json($scheme);
    
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
            console.log('⏰ Auto refresh triggered at', new Date().toLocaleTimeString());
            @this.refreshData('auto');
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
        let interval = @js($refreshInterval);
        if (Array.isArray(event) && event[0] && event[0].interval) {
            interval = event[0].interval;
        } else if (event && event.interval) {
            interval = event.interval;
        }
        stopAutoRefresh();
        startAutoRefresh(interval);
    });
    Livewire.on('restart-auto-refresh-delayed', (event) => {
        console.log('🔄 Restarting auto refresh with EXTENDED delay');
        let interval = @js($refreshInterval);
        if (Array.isArray(event) && event[0] && event[0].interval) {
            interval = event[0].interval;
        } else if (event && event.interval) {
            interval = event.interval;
        }
        stopAutoRefresh();
        setTimeout(() => {
            if (@js($autoRefresh)) {
                console.log('🟢 Starting delayed auto refresh after aggregation change');
                startAutoRefresh(interval);
            } else {
                console.log('⏸️ Auto refresh disabled, not restarting');
            }
        }, 5000);
    });
    Livewire.on('update-refresh-interval', (event) => {
        let interval = @js($refreshInterval);
        if (event && event.interval) {
            interval = event.interval;
        }
        startAutoRefresh(interval);
    });
    
    window.addEventListener('beforeunload', stopAutoRefresh);
    
    // Mulai auto refresh jika enabled dari server
    if (@js($autoRefresh)) {
        startAutoRefresh(@js($refreshInterval));
    }   
    Livewire.on('data-refreshed', (event) => {
        updateLastUpdatedTime();
        // Debug info
        let hasNewData = false;
        if (event && typeof event === 'object') {
            hasNewData = event.hasNewData || false;
        }
        console.log('🔄 Data refreshed event received. New data:', hasNewData);
    });
    
    Livewire.on('chart-data-updated', (event) => {
        // Ambil data terbaru
        let processedData = event.processedData;
        let scheme = event.scheme;
        if (!processedData || processedData.length === 0) return;
        
        // Cek timestamp data terakhir
        const latest = processedData[processedData.length - 1];
        const latestTimestamp = latest.created_at || latest.timestamp || null;
        
        // Hanya update jika data terbaru berubah
        if (latestTimestamp !== lastBarDataTimestamp) {
            lastBarDataTimestamp = latestTimestamp;
            currentProcessedData = processedData;
            currentScheme = scheme;
            renderBarChart(processedData, scheme, selectedBarRecord);
            console.log('✅ Bar chart updated with new data at', latestTimestamp);
        } else {
            console.log('⏸️ No new data for bar chart');
        }
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

