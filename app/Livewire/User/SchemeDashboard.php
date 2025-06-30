<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Scheme;
use App\Models\DataIot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;

class SchemeDashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';
    
    public $scheme;
    public $schemeId;
    public $timeRange = '7d';
    public $dateFrom;
    public $dateTo;
    public $chartData = [];
    public $processedData = [];
    public $perPage = 10;
    public $autoRefresh = true;
    public $refreshInterval = 30;
    public $lastDataCount = 0;
    
    // New properties for data aggregation
    public $dataAggregation = 'raw'; // raw, hourly, daily, weekly, monthly
    public $aggregationFunction = 'avg'; // avg, median
    public $showRawData = true; // Toggle between aggregated and raw data view
    
    // Tambahkan property:
    public $selectedBarRecord = null;
    
    protected $queryString = [
        'timeRange' => ['except' => '7d'],
        'perPage' => ['except' => 10],
        'dataAggregation' => ['except' => 'raw'],
        'aggregationFunction' => ['except' => 'avg'],
        'showRawData' => ['except' => true]
        // HAPUS: 'autoRefresh' dan 'refreshInterval' karena tidak perlu di URL
    ];
    
    public function mount($schemeId)
    {
        $this->schemeId = $schemeId;
        
        // Load scheme dengan relasi yang diperlukan
        $this->scheme = Scheme::with(['sensors' => function($query) {
            $query->withPivot('alias', 'order')->orderBy('scheme_sensors.order');
        }])->findOrFail($schemeId);
        
        // Verify user access to this scheme
        if ($this->scheme->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to scheme');
        }
        
        $this->setDateRange();
        $this->loadData();
        $this->resetSelectedBarRecord();
        $this->lastDataCount = $this->getDataCount();
    }
    
    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
        if ($this->autoRefresh) {
            $this->dispatch('start-auto-refresh');
        } else {
            $this->dispatch('stop-auto-refresh');
        }
    }
    
    public function updateRefreshInterval($interval)
    {
        $this->refreshInterval = max(5, min(300, $interval));
        if ($this->autoRefresh) {
            $this->dispatch('update-refresh-interval', interval: $this->refreshInterval);
        }
    }
    
    private function getDataCount()
    {
        // PERBAIKAN: Hitung SEMUA raw data untuk scheme ini tanpa filter time range
        // Ini akan menjadi acuan yang stabil untuk deteksi data baru
        $count = DataIot::where('scheme_id', $this->schemeId)->count();
        
        // Enhanced debug logging
        \Log::info('Raw data total count check', [
            'scheme_id' => $this->schemeId,
            'total_raw_count' => $count,
            'last_stored_count' => $this->lastDataCount,
            'has_change' => $count !== $this->lastDataCount,
            'current_time_range' => $this->timeRange,
            'aggregation_type' => $this->dataAggregation
        ]);
        
        return $count;
    }
    
    private function getFilteredDataCount()
    {
        // Method terpisah untuk mendapatkan count data yang sudah difilter time range
        // Ini hanya untuk informasi/logging, bukan untuk deteksi data baru
        $query = DataIot::where('scheme_id', $this->schemeId);
        
        if ($this->timeRange === '24h') {
            $query->whereBetween('created_at', [
                $this->dateFrom, 
                $this->dateTo
            ]);
        } else {
            $query->whereBetween('created_at', [
                $this->dateFrom, 
                Carbon::parse($this->dateTo)->endOfDay()
            ]);
        }
        
        return $query->count();
    }

    public function refreshData($context = 'auto')
    {
        // Get TOTAL raw data count (tidak difilter time range)
        $currentTotalDataCount = $this->getDataCount();
        
        // Get filtered data count untuk informasi
        $currentFilteredDataCount = $this->getFilteredDataCount();
        
        // Debug log untuk tracking
        \Log::info('RefreshData called', [
            'context' => $context,
            'refresh_interval' => $this->refreshInterval,
            'auto_refresh' => $this->autoRefresh,
            'aggregation_type' => $this->dataAggregation,
            'time_range' => $this->timeRange,
            'total_raw_count' => $currentTotalDataCount,
            'filtered_data_count' => $currentFilteredDataCount,
            'last_total_count' => $this->lastDataCount
        ]);
        
        // PERBAIKAN: Deteksi data baru HANYA berdasarkan total raw data count
        $hasNewData = false;
        if ($context === 'auto') {
            // Auto refresh: HANYA jika ada perubahan TOTAL raw data count
            $hasNewData = ($currentTotalDataCount !== $this->lastDataCount);
            
            if ($hasNewData) {
                \Log::info('New raw data detected (total count changed)', [
                    'previous_total_count' => $this->lastDataCount,
                    'new_total_count' => $currentTotalDataCount,
                    'difference' => $currentTotalDataCount - $this->lastDataCount,
                    'filtered_count' => $currentFilteredDataCount,
                    'time_range' => $this->timeRange
                ]);
            } else {
                \Log::info('No new raw data detected (total count unchanged)', [
                    'total_count' => $currentTotalDataCount,
                    'filtered_count' => $currentFilteredDataCount,
                    'time_range' => $this->timeRange
                ]);
            }
        } else {
            // Manual refresh: selalu load data fresh
            $hasNewData = true;
            \Log::info('Manual refresh - forcing data update', [
                'total_raw_count' => $currentTotalDataCount,
                'filtered_count' => $currentFilteredDataCount,
                'last_total_count' => $this->lastDataCount
            ]);
        }
        
        // Load fresh data jika ada perubahan atau manual refresh
        if ($hasNewData) {
            $this->loadData();
            $this->resetSelectedBarRecord();
            // Hanya dispatch chart update jika perlu
            if ($context === 'manual' || $currentTotalDataCount !== $this->lastDataCount) {
                // Reload scheme with fresh relations
                $this->scheme = Scheme::with(['sensors' => function($query) {
                    $query->withPivot('alias', 'order')->orderBy('scheme_sensors.order');
                }])->findOrFail($this->schemeId);
                
                // Prepare scheme data for chart
                $schemeData = [
                    'id' => $this->scheme->id,
                    'name' => $this->scheme->name,
                    'description' => $this->scheme->description,
                    'visualization_type' => $this->scheme->visualization_type,
                    'additional_columns' => $this->scheme->additional_columns,
                    'sensors' => $this->scheme->sensors->map(function($sensor) {
                        return [
                            'id' => $sensor->id,
                            'name' => $sensor->name,
                            'description' => $sensor->description,
                            'num_of_outputs' => $sensor->num_of_outputs,
                            'output_labels' => $sensor->output_labels,
                            'picture' => $sensor->picture,
                            'validation_settings' => $sensor->validation_settings,
                            'pivot' => [
                                'alias' => $sensor->pivot->alias ?? null,
                                'order' => $sensor->pivot->order ?? 0,
                            ]
                        ];
                    })->toArray()
                ];
                
                // Get latest timestamp for reference dari processed data
                $latestTimestamp = null;
                if (!empty($this->processedData)) {
                    $latestTimestamp = $this->processedData[count($this->processedData) - 1]['created_at'];
                }
                
                // Dispatch chart update event
                $this->dispatch('chart-data-updated', 
                    processedData: $this->processedData,
                    scheme: $schemeData,
                    aggregation: $this->dataAggregation,
                    hasNewData: true,
                    latestTimestamp: $latestTimestamp,
                    context: $context,
                    totalDataCount: $currentTotalDataCount,
                    filteredDataCount: $currentFilteredDataCount
                );
                
                // Reset pagination untuk manual refresh atau data baru
                if ($context === 'manual' || $currentTotalDataCount !== $this->lastDataCount) {
                    $this->resetPage();
                }
                
                \Log::info('Chart updated with data', [
                    'context' => $context,
                    'aggregation_type' => $this->dataAggregation,
                    'processed_data_count' => count($this->processedData),
                    'total_raw_count' => $currentTotalDataCount,
                    'filtered_raw_count' => $currentFilteredDataCount
                ]);
            }
        } else {
            \Log::info('No chart update needed - no new raw data', [
                'context' => $context,
                'aggregation_type' => $this->dataAggregation,
                'total_raw_count' => $currentTotalDataCount,
                'filtered_count' => $currentFilteredDataCount,
                'last_total_count' => $this->lastDataCount
            ]);
        }
        
        // Always dispatch data-refreshed for UI updates (timestamp, etc.)
        $this->dispatch('data-refreshed', 
            hasNewData: $hasNewData,
            context: $context,
            totalDataCount: $currentTotalDataCount,
            filteredDataCount: $currentFilteredDataCount
        );
        
        // PENTING: Update last TOTAL data count (bukan filtered count)
        $this->lastDataCount = $currentTotalDataCount;

        
    }
    
    private function setDateRange()
    {
        $now = Carbon::now();
        
        switch ($this->timeRange) {
            case '24h':
                // Fix: Use subHours(24) for exact 24 hours with full datetime format
                $this->dateFrom = $now->copy()->subHours(24)->format('Y-m-d H:i:s');
                $this->dateTo = $now->format('Y-m-d H:i:s');
                break;
            case '7d':
                $this->dateFrom = $now->copy()->subDays(7)->toDateString();
                $this->dateTo = $now->toDateString();
                break;
            case '30d':
                $this->dateFrom = $now->copy()->subDays(30)->toDateString();
                $this->dateTo = $now->toDateString();
                break;
            case 'all':
                // Get the earliest data from this scheme
                $earliestData = DataIot::where('scheme_id', $this->schemeId)
                    ->orderBy('created_at', 'asc')
                    ->first();
                
                if ($earliestData) {
                    $this->dateFrom = $earliestData->created_at->toDateString();
                } else {
                    // Fallback if no data exists
                    $this->dateFrom = $now->copy()->subYear()->toDateString();
                }
                $this->dateTo = $now->toDateString();
                break;
            case 'custom':
                // Keep existing dateFrom and dateTo values
                if (!$this->dateFrom) {
                    $this->dateFrom = $now->copy()->subDays(7)->toDateString();
                }
                if (!$this->dateTo) {
                    $this->dateTo = $now->toDateString();
                }
                break;
            default:
                $this->dateFrom = $now->copy()->subHours(24)->format('Y-m-d H:i:s');
                $this->dateTo = $now->format('Y-m-d H:i:s');
                break;
        }
        
        \Log::info('Date range set', [
            'timeRange' => $this->timeRange,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'current_time' => $now->format('Y-m-d H:i:s')
        ]);
    }
    
    public function updatedTimeRange()
    {
        // Time range change TIDAK boleh mengubah lastDataCount
        // karena total raw data tidak berubah
        
        $this->setDateRange();
        $this->loadData();
        $this->resetPage();
        
        $schemeData = [
            'id' => $this->scheme->id,
            'name' => $this->scheme->name,
            'description' => $this->scheme->description,
            'visualization_type' => $this->scheme->visualization_type,
            'additional_columns' => $this->scheme->additional_columns,
            'sensors' => $this->scheme->sensors->map(function($sensor) {
                return [
                    'id' => $sensor->id,
                    'name' => $sensor->name,
                    'description' => $sensor->description,
                    'num_of_outputs' => $sensor->num_of_outputs,
                    'output_labels' => $sensor->output_labels,
                    'picture' => $sensor->picture,
                    'validation_settings' => $sensor->validation_settings,
                    'pivot' => [
                        'alias' => $sensor->pivot->alias ?? null,
                        'order' => $sensor->pivot->order ?? 0,
                    ]
                ];
            })->toArray()
        ];
        
        $this->dispatch('chart-data-updated', 
            processedData: $this->processedData,
            scheme: $schemeData,
            isTimeRangeChange: true,
            timeRange: $this->timeRange,
            context: 'time_range_change',
            totalDataCount: $this->getDataCount(), // Get current total, tapi jangan update lastDataCount
            filteredDataCount: $this->getFilteredDataCount()
        );
        
        \Log::info('Time range updated', [
            'new_time_range' => $this->timeRange,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'processed_data_count' => count($this->processedData),
            'total_data_count' => $this->getDataCount(),
            'filtered_data_count' => $this->getFilteredDataCount(),
            'last_data_count_unchanged' => $this->lastDataCount // This should NOT change
        ]);
    }

    public function updatedDateFrom()
    {
        if ($this->timeRange === 'custom') {
            $this->loadData();
            $this->resetPage();
            
            $schemeData = [
                'id' => $this->scheme->id,
                'name' => $this->scheme->name,
                'description' => $this->scheme->description,
                'visualization_type' => $this->scheme->visualization_type,
                'additional_columns' => $this->scheme->additional_columns,
                'sensors' => $this->scheme->sensors->map(function($sensor) {
                    return [
                        'id' => $sensor->id,
                        'name' => $sensor->name,
                        'description' => $sensor->description,
                        'num_of_outputs' => $sensor->num_of_outputs,
                        'output_labels' => $sensor->output_labels,
                        'picture' => $sensor->picture,
                        'validation_settings' => $sensor->validation_settings,
                        'pivot' => [
                            'alias' => $sensor->pivot->alias ?? null,
                            'order' => $sensor->pivot->order ?? 0,
                        ]
                    ];
                })->toArray()
            ];
            
            $this->dispatch('chart-data-updated', 
                processedData: $this->processedData,
                scheme: $schemeData,
                isTimeRangeChange: true,
                aggregation: $this->dataAggregation
            );
        }
    }

    public function updatedDateTo()
    {
        if ($this->timeRange === 'custom') {
            $this->loadData();
            $this->resetPage();
            
            $schemeData = [
                'id' => $this->scheme->id,
                'name' => $this->scheme->name,
                'description' => $this->scheme->description,
                'visualization_type' => $this->scheme->visualization_type,
                'additional_columns' => $this->scheme->additional_columns,
                'sensors' => $this->scheme->sensors->map(function($sensor) {
                    return [
                        'id' => $sensor->id,
                        'name' => $sensor->name,
                        'description' => $sensor->description,
                        'num_of_outputs' => $sensor->num_of_outputs,
                        'output_labels' => $sensor->output_labels,
                        'picture' => $sensor->picture,
                        'validation_settings' => $sensor->validation_settings,
                        'pivot' => [
                            'alias' => $sensor->pivot->alias ?? null,
                            'order' => $sensor->pivot->order ?? 0,
                        ]
                    ];
                })->toArray()
            ];
            
            $this->dispatch('chart-data-updated', 
                processedData: $this->processedData,
                scheme: $schemeData,
                isTimeRangeChange: true,
                aggregation: $this->dataAggregation
            );
        }
    }

    public function applyCustomDateRange()
    {
        if ($this->timeRange === 'custom') {
            $this->loadData();
            $this->resetPage();
            
            $schemeData = [
                'id' => $this->scheme->id,
                'name' => $this->scheme->name,
                'description' => $this->scheme->description,
                'visualization_type' => $this->scheme->visualization_type,
                'additional_columns' => $this->scheme->additional_columns,
                'sensors' => $this->scheme->sensors->map(function($sensor) {
                    return [
                        'id' => $sensor->id,
                        'name' => $sensor->name,
                        'description' => $sensor->description,
                        'num_of_outputs' => $sensor->num_of_outputs,
                        'output_labels' => $sensor->output_labels,
                        'picture' => $sensor->picture,
                        'validation_settings' => $sensor->validation_settings,
                        'pivot' => [
                            'alias' => $sensor->pivot->alias ?? null,
                            'order' => $sensor->pivot->order ?? 0,
                        ]
                    ];
                })->toArray()
            ];
            
            $this->dispatch('chart-data-updated', 
                processedData: $this->processedData,
                scheme: $schemeData,
                isTimeRangeChange: true,
                aggregation: $this->dataAggregation
            );
        }
    }
    
    public function updatedDataAggregation()
    {
        // Stop auto refresh sementara
        $this->dispatch('stop-auto-refresh');
        
        $this->loadData();
        $this->resetPage();
        
        // PENTING: Sync total count untuk mencegah false positive
        $this->lastDataCount = $this->getDataCount();
        //$this->showRawData = ($value === 'raw');
        $this->resetSelectedBarRecord();
        
        $schemeData = [
            'id' => $this->scheme->id,
            'name' => $this->scheme->name,
            'description' => $this->scheme->description,
            'visualization_type' => $this->scheme->visualization_type,
            'additional_columns' => $this->scheme->additional_columns,
            'sensors' => $this->scheme->sensors->map(function($sensor) {
                return [
                    'id' => $sensor->id,
                    'name' => $sensor->name,
                    'description' => $sensor->description,
                    'num_of_outputs' => $sensor->num_of_outputs,
                    'output_labels' => $sensor->output_labels,
                    'picture' => $sensor->picture,
                    'validation_settings' => $sensor->validation_settings,
                    'pivot' => [
                        'alias' => $sensor->pivot->alias ?? null,
                        'order' => $sensor->pivot->order ?? 0,
                    ]
                ];
            })->toArray()
        ];
        
        // Dispatch chart update dengan flag isAggregationChange
        $this->dispatch('chart-data-updated', 
            processedData: $this->processedData,
            scheme: $schemeData,
            isAggregationChange: true,
            aggregation: $this->dataAggregation,
            context: 'aggregation_change',
            totalDataCount: $this->lastDataCount,
            filteredDataCount: $this->getFilteredDataCount()
        );
        
        // Restart auto refresh dengan delay
        if ($this->autoRefresh) {
            $this->dispatch('restart-auto-refresh-delayed', interval: $this->refreshInterval);
        }
        
        \Log::info('Data aggregation updated', [
            'new_aggregation' => $this->dataAggregation,
            'processed_data_count' => count($this->processedData),
            'total_data_count' => $this->lastDataCount,
            'filtered_data_count' => $this->getFilteredDataCount(),
            'auto_refresh' => $this->autoRefresh
        ]);
    }
    
    public function updatedAggregationFunction()
    {
        if ($this->dataAggregation !== 'raw') {
            // Stop auto refresh sementara
            $this->dispatch('stop-auto-refresh');
            
            $this->loadData();
            $this->resetPage();
            
            // Sync total count
            $this->lastDataCount = $this->getDataCount();
            
            $schemeData = [
                'id' => $this->scheme->id,
                'name' => $this->scheme->name,
                'description' => $this->scheme->description,
                'visualization_type' => $this->scheme->visualization_type,
                'additional_columns' => $this->scheme->additional_columns,
                'sensors' => $this->scheme->sensors->map(function($sensor) {
                    return [
                        'id' => $sensor->id,
                        'name' => $sensor->name,
                        'description' => $sensor->description,
                        'num_of_outputs' => $sensor->num_of_outputs,
                        'output_labels' => $sensor->output_labels,
                        'picture' => $sensor->picture,
                        'validation_settings' => $sensor->validation_settings,
                        'pivot' => [
                            'alias' => $sensor->pivot->alias ?? null,
                            'order' => $sensor->pivot->order ?? 0,
                        ]
                    ];
                })->toArray()
            ];
            
            // Dispatch chart update dengan flag isAggregationChange
            $this->dispatch('chart-data-updated', 
                processedData: $this->processedData,
                scheme: $schemeData,
                isAggregationChange: true,
                aggregation: $this->dataAggregation,
                context: 'aggregation_change',
                totalDataCount: $this->lastDataCount,
                filteredDataCount: $this->getFilteredDataCount()
            );
            
            // Restart auto refresh dengan delay
            if ($this->autoRefresh) {
                $this->dispatch('restart-auto-refresh-delayed', interval: $this->refreshInterval);
            }
            
            \Log::info('Aggregation function updated', [
                'new_function' => $this->aggregationFunction,
                'aggregation_type' => $this->dataAggregation,
                'processed_data_count' => count($this->processedData),
                'total_data_count' => $this->lastDataCount,
                'filtered_data_count' => $this->getFilteredDataCount(),
                'auto_refresh' => $this->autoRefresh
            ]);
        }
    }
    
    // New method to toggle between raw and aggregated data view
    public function toggleDataView()
    {
        $this->showRawData = !$this->showRawData;
        $this->resetPage(); // Reset pagination when toggling view
        $this->resetSelectedBarRecord();
        
        \Log::info('Data view toggled', [
            'showRawData' => $this->showRawData,
            'dataAggregation' => $this->dataAggregation
        ]);
    }
    
    public function updatedPerPage()
    {
        $this->resetPage();
    }
    
    public function loadData()
    {
        if ($this->dataAggregation === 'raw') {
            $this->loadRawData();
        } else {
            $this->loadAggregatedData();
        }
    }
    
    private function loadRawData()
    {
        // Determine limit based on time range
        $limit = $this->timeRange === 'all' ? 1000 : 500; // Increase limit for "all" but still reasonable
        
        $query = DataIot::where('scheme_id', $this->schemeId);
        
        if ($this->timeRange === '24h') {
            // For 24h, use exact datetime comparison
            $query->whereBetween('created_at', [
                $this->dateFrom, 
                $this->dateTo
            ]);
        } else {
            // For other ranges, use end of day
            $query->whereBetween('created_at', [
                $this->dateFrom, 
                Carbon::parse($this->dateTo)->endOfDay()
            ]);
        }
        
        // Get the data for chart (limited amount for performance)
        $this->chartData = $query
            ->orderBy('id')
            ->limit($limit)
            ->get();
            
        // Process the data for chart display
        $this->processedData = [];
        
        foreach ($this->chartData as $data) {
            try {
                $jsonData = $data->json_content;
                if (is_string($jsonData)) {
                    $jsonData = json_decode($jsonData, true);
                }
                if (!is_array($jsonData)) {
                    continue;
                }
                $processedItem = [
                    'id' => $data->id,
                    'created_at' => $data->created_at->toISOString(),
                    'sensors' => [],
                    'additional_content' => $data->additional_content
                ];
                foreach ($jsonData as $sensorData) {
                    $sensorValues = [];
                    if (isset($sensorData['values']) && is_array($sensorData['values'])) {
                        foreach ($sensorData['values'] as $valueData) {
                            $sensorValues[$valueData['label']] = $valueData['value'];
                        }
                    }
                    $processedItem['sensors'][] = [
                        'id' => $sensorData['id'] ?? null,
                        'name' => $sensorData['name'] ?? 'Unknown',
                        // Perbaikan: simpan alias dari data, bukan dari pivot
                        'alias' => $sensorData['alias'] ?? ($sensorData['pivot']['alias'] ?? null),
                        'values' => $sensorValues
                    ];
                }
                $this->processedData[] = $processedItem;
            } catch (\Exception $e) {
                Log::error('Error processing data: ' . $e->getMessage(), [
                    'data_id' => $data->id,
                    'content' => $data->json_content
                ]);
            }
        }
        
        \Log::info('Raw data loaded', [
            'timeRange' => $this->timeRange,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'chartData_count' => $this->chartData->count(),
            'processedData_count' => count($this->processedData),
            'limit_applied' => $limit
        ]);
    }
    
    private function loadAggregatedData()
    {
        // Get aggregation period and SQL format
        $periodInfo = $this->getAggregationPeriodInfo();
        
        if (!$periodInfo) {
            $this->loadRawData(); // Fallback to raw data
            return;
        }
        
        $query = DataIot::where('scheme_id', $this->schemeId);
        
        if ($this->timeRange === '24h') {
            // For 24h, use exact datetime comparison
            $query->whereBetween('created_at', [
                $this->dateFrom, 
                $this->dateTo
            ]);
        } else {
            // For other ranges, use end of day
            $query->whereBetween('created_at', [
                $this->dateFrom, 
                Carbon::parse($this->dateTo)->endOfDay()
            ]);
        }
        
        // Get all data for aggregation
        $rawData = $query->orderBy('id')->get();
        
        // Process and aggregate the data
        $this->processedData = $this->aggregateData($rawData, $periodInfo);
        
        \Log::info('Aggregated data loaded', [
            'aggregation' => $this->dataAggregation,
            'aggregationFunction' => $this->aggregationFunction,
            'rawData_count' => $rawData->count(),
            'processedData_count' => count($this->processedData)
        ]);
    }
    
    private function getAggregationPeriodInfo()
    {
        switch ($this->dataAggregation) {
            case 'hourly':
                return [
                    'format' => 'Y-m-d H:00:00',
                    'carbonFormat' => 'Y-m-d H:00:00',
                    'label' => 'Hour'
                ];
            case 'daily':
                return [
                    'format' => 'Y-m-d',
                    'carbonFormat' => 'Y-m-d',
                    'label' => 'Day'
                ];
            case 'weekly':
                return [
                    'format' => 'Week-m/Y',
                    'carbonFormat' => 'Y-m-d 00:00:00',
                    'label' => 'Week'
                ];
            case 'monthly':
                return [
                    'format' => 'm/Y',
                    'carbonFormat' => 'Y-m-01 00:00:00',
                    'label' => 'Month'
                ];
            case 'quarterly':
                return [
                    'format' => 'Q-Y',
                    'carbonFormat' => 'Y-m-01 00:00:00',
                    'label' => 'Quarter'
                ];
            default:
                return null;
        }
    }
    
    private function aggregateData($rawData, $periodInfo)
    {
        $groupedData = [];
        
        foreach ($rawData as $data) {
            try {
                // Get the period key based on aggregation type
                $periodKey = $this->getPeriodKey($data->created_at, $periodInfo);
                
                if (!isset($groupedData[$periodKey])) {
                    $groupedData[$periodKey] = [
                        'period' => $periodKey,
                        'timestamp' => $this->getPeriodTimestamp($data->created_at, $periodInfo),
                        'data_points' => []
                    ];
                }
                
                // Process JSON content
                $jsonData = $data->json_content;
                if (is_string($jsonData)) {
                    $jsonData = json_decode($jsonData, true);
                }
                
                if (is_array($jsonData)) {
                    $groupedData[$periodKey]['data_points'][] = [
                        'created_at' => $data->created_at,
                        'sensors' => $jsonData,
                        'additional_content' => $data->additional_content
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Error grouping data: ' . $e->getMessage(), [
                    'data_id' => $data->id
                ]);
            }
        }
        
        // Now aggregate each group
        $aggregatedData = [];
        foreach ($groupedData as $periodKey => $group) {
            $aggregatedItem = $this->aggregateGroupData($group, $periodInfo);
            if ($aggregatedItem) {
                $aggregatedData[] = $aggregatedItem;
            }
        }
        
        // Sort by timestamp
        usort($aggregatedData, function($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
        
        return $aggregatedData;
    }
    
    private function getPeriodKey($timestamp, $periodInfo)
    {
        $carbon = Carbon::parse($timestamp);
        
        switch ($this->dataAggregation) {
            case 'hourly':
                return $carbon->format('Y-m-d H:00:00');
            case 'daily':
                return $carbon->format('Y-m-d');
            case 'weekly':
                // Get week number and format as "Week X MM/YY"
                $weekOfMonth = ceil($carbon->day / 7);
                return 'Week ' . $weekOfMonth . ' ' . $carbon->format('m/y');
            case 'monthly':
                return $carbon->format('m/Y');
            case 'quarterly':
                $quarter = ceil($carbon->month / 3);
                return 'Q' . $quarter . ' ' . $carbon->year;
            default:
                return $carbon->format('Y-m-d H:i:s');
        }
    }
    
    private function getPeriodTimestamp($timestamp, $periodInfo)
    {
        $carbon = Carbon::parse($timestamp);
        
        switch ($this->dataAggregation) {
            case 'hourly':
                return $carbon->startOfHour();
            case 'daily':
                return $carbon->startOfDay();
            case 'weekly':
                // Use start of week for timestamp
                return $carbon->startOfWeek();
            case 'monthly':
                return $carbon->startOfMonth();
            case 'quarterly':
                // Calculate quarter start
                $quarter = ceil($carbon->month / 3);
                $quarterStartMonth = ($quarter - 1) * 3 + 1;
                return $carbon->setMonth($quarterStartMonth)->startOfMonth();
            default:
                return $carbon;
        }
    }
    
    private function aggregateGroupData($group, $periodInfo)
    {
        if (empty($group['data_points'])) {
            return null;
        }
        
        // Format timestamp based on aggregation type
        $formattedTimestamp = $group['timestamp'];
        switch ($this->dataAggregation) {
            case 'daily':
                $displayTime = $formattedTimestamp->format('Y-m-d');
                break;
            case 'weekly':
                $weekOfMonth = ceil($formattedTimestamp->day / 7);
                $displayTime = 'Week ' . $weekOfMonth . ' ' . $formattedTimestamp->format('M Y');
                break;
            case 'monthly':
                $displayTime = $formattedTimestamp->format('M Y');
                break;
            case 'quarterly':
                $quarter = ceil($formattedTimestamp->month / 3);
                $displayTime = 'Q' . $quarter . ' ' . $formattedTimestamp->year;
                break;
            default:
                $displayTime = $formattedTimestamp->toISOString();
        }
        
        // Initialize aggregated item
        $aggregatedItem = [
            'id' => 'agg_' . $group['period'],
            'created_at' => $group['timestamp']->toISOString(),
            'period' => $group['period'],
            'display_time' => $displayTime,
            'data_count' => count($group['data_points']),
            'sensors' => [],
            'additional_content' => []
        ];
        
        // Group by sensor
        $sensorGroups = [];
        foreach ($group['data_points'] as $point) {
            foreach ($point['sensors'] as $sensorData) {
                $sensorId = $sensorData['id'] ?? null;
                $sensorAlias = $sensorData['alias'] ?? ($sensorData['pivot']['alias'] ?? null);
                $sensorKey = $sensorId . '_' . ($sensorAlias ?? '');
                if (!isset($sensorGroups[$sensorKey])) {
                    $sensorGroups[$sensorKey] = [
                        'id' => $sensorId,
                        'name' => $sensorData['name'] ?? 'Unknown',
                        'alias' => $sensorAlias,
                        'values' => []
                    ];
                }
                
                // Collect all values for this sensor
                if (isset($sensorData['values']) && is_array($sensorData['values'])) {
                    foreach ($sensorData['values'] as $valueData) {
                        $label = $valueData['label'];
                        if (!isset($sensorGroups[$sensorKey]['values'][$label])) {
                            $sensorGroups[$sensorKey]['values'][$label] = [];
                        }
                        
                        $numericValue = is_numeric($valueData['value']) ? floatval($valueData['value']) : null;
                        if ($numericValue !== null) {
                            $sensorGroups[$sensorKey]['values'][$label][] = $numericValue;
                        }
                    }
                }
            }
        }
        
        // Apply aggregation function to each sensor value
        foreach ($sensorGroups as $sensorKey => $sensorGroup) {
            $aggregatedSensor = [
                'id' => $sensorGroup['id'],
                'name' => $sensorGroup['name'],
                'alias' => $sensorGroup['alias'],
                'values' => []
            ];
            
            foreach ($sensorGroup['values'] as $label => $values) {
                if (!empty($values)) {
                    $aggregatedValue = $this->applyAggregationFunction($values);
                    $aggregatedSensor['values'][$label] = round($aggregatedValue, 2);
                }
            }
            
            $aggregatedItem['sensors'][] = $aggregatedSensor;
        }
        
        return $aggregatedItem;
    }
    
    private function applyAggregationFunction($values)
    {
        if (empty($values)) {
            return null;
        }
        
        switch ($this->aggregationFunction) {
            case 'avg':
                return array_sum($values) / count($values);
            case 'median':
                sort($values);
                $count = count($values);
                $middle = floor($count / 2);
                
                if ($count % 2 == 0) {
                    // Even number of values - average of two middle values
                    return ($values[$middle - 1] + $values[$middle]) / 2;
                } else {
                    // Odd number of values - middle value
                    return $values[$middle];
                }
            default:
                return array_sum($values) / count($values); // Default to average
        }
    }
    
    public function getPaginatedDataProperty()
    {
        if ($this->showRawData || $this->dataAggregation === 'raw') {
            $query = DataIot::where('scheme_id', $this->schemeId);
            
            if ($this->timeRange === '24h') {
                // For 24h, use exact datetime comparison
                $query->whereBetween('created_at', [
                    $this->dateFrom, 
                    $this->dateTo
                ]);
            } else {
                // For other ranges, use end of day
                $query->whereBetween('created_at', [
                    $this->dateFrom, 
                    Carbon::parse($this->dateTo)->endOfDay()
                ]);
            }
            
            return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
        } else {
            // For aggregated data, we'll use a manual pagination approach
            $aggregatedData = collect($this->processedData);
            $page = request()->get('page', 1);
            $perPage = $this->perPage;
            $offset = ($page - 1) * $perPage;
            
            $items = $aggregatedData->slice($offset, $perPage)->values();
            
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $aggregatedData->count(),
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
        }
    }
    
    public function exportData($format)
    {
        // Determine if we're exporting raw data based on aggregation type and toggle state
        $exportingRawData = ($this->dataAggregation === 'raw') || ($this->dataAggregation !== 'raw' && $this->showRawData);
        
        if ($exportingRawData) {
            $query = DataIot::where('scheme_id', $this->schemeId);
            
            if ($this->timeRange === '24h') {
                // For 24h, use exact datetime comparison
                $query->whereBetween('created_at', [
                    $this->dateFrom, 
                    $this->dateTo
                ]);
            } else {
                // For other ranges, use end of day
                $query->whereBetween('created_at', [
                    $this->dateFrom, 
                    Carbon::parse($this->dateTo)->endOfDay()
                ]);
            }
            
            $data = $query->orderBy('created_at', 'desc')->get();
            $exportType = 'raw';
        } else {
            // For aggregated data, use processed data
            $data = collect($this->processedData);
            $exportType = $this->dataAggregation;
        }
        
        $filename = 'scheme_data_' . $this->scheme->name . '_' . $exportType . '_' . date('Y-m-d') . '.' . $format;
        
        if ($format === 'csv') {
            return $this->exportToCsv($data, $filename, $exportingRawData);
        } else {
            return $this->exportToExcel($data, $filename, $exportingRawData);
        }
    }
    
    protected function exportToCsv($data, $filename, $isRawData = null)
    {
        // Auto-detect if not provided
        if ($isRawData === null) {
            $isRawData = ($this->dataAggregation === 'raw') || ($this->dataAggregation !== 'raw' && $this->showRawData);
        }
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($data, $isRawData) {
            $handle = fopen('php://output', 'w');
            
            // Create headers row
            $headers = ['Timestamp'];
            
            // Add aggregation info if not raw data
            if (!$isRawData) {
                $headers[] = 'Period';
                $headers[] = 'Data Points Count';
                $headers[] = 'Aggregation Function';
            }
            
            // Add sensor headers
            foreach ($this->scheme->sensors as $sensor) {
                $outputs = $sensor->num_of_outputs ?: 1;
                $outputLabels = explode(',', $sensor->output_labels ?? '');
                
                for ($i = 0; $i < $outputs; $i++) {
                    $sensorHeader = ($sensor->pivot->alias ?: $sensor->name);
                    if ($outputs > 1) {
                        $sensorHeader .= ' (' . (isset($outputLabels[$i]) ? $outputLabels[$i] : "Output ".($i+1)) . ')';
                    }
                    if (!$isRawData) {
                        $functionLabel = $this->aggregationFunction === 'avg' ? 'AVG' : 'MEDIAN';
                        $sensorHeader .= ' (' . $functionLabel . ')';
                    }
                    $headers[] = $sensorHeader;
                }
            }
            
            fputcsv($handle, $headers);
            
            // Add data rows
            foreach ($data as $row) {
                if ($isRawData) {
                    // Handle raw data export (existing logic)
                    $csvRow = [
                        is_object($row) ? $row->created_at->format('Y-m-d H:i:s') : $row['created_at'],
                    ];
                    $jsonData = is_object($row) ? $row->json_content : $row['sensors'];
                    if (is_string($jsonData)) {
                        $jsonData = json_decode($jsonData, true);
                    }
                    foreach ($this->scheme->sensors as $sensor) {
                        $outputs = $sensor->num_of_outputs ?: 1;
                        $outputLabels = explode(',', $sensor->output_labels ?? '');
                        // Perbaikan: cari sensor dengan id dan alias
                        $sensorData = null;
                        if (is_array($jsonData)) {
                            foreach ($jsonData as $sensorJson) {
                                $jsonId = isset($sensorJson['id']) ? $sensorJson['id'] : null;
                                $jsonAlias = $sensorJson['alias'] ?? ($sensorJson['pivot']['alias'] ?? null);
                                $pivotAlias = $sensor->pivot->alias ?? null;
                                if ($jsonId == $sensor->id && (string)$jsonAlias === (string)$pivotAlias) {
                                    $sensorData = $sensorJson;
                                    break;
                                }
                            }
                        }
                        if ($sensorData && isset($sensorData['values'])) {
                            for ($i = 0; $i < $outputs; $i++) {
                                $label = isset($outputLabels[$i]) ? trim($outputLabels[$i]) : "Value " . ($i + 1);
                                $value = '';
                                foreach ($sensorData['values'] as $valueData) {
                                    if ($valueData['label'] == $label) {
                                        $value = $valueData['value'];
                                        break;
                                    }
                                }
                                $csvRow[] = $value;
                            }
                        } else {
                            for ($i = 0; $i < $outputs; $i++) {
                                $csvRow[] = '';
                            }
                        }
                    }
                } else {
                    // Handle aggregated data export
                    $functionLabel = $this->aggregationFunction === 'avg' ? 'AVG' : 'MEDIAN';
                    $csvRow = [
                        Carbon::parse($row['created_at'])->format('Y-m-d H:i:s'),
                        $row['period'],
                        $row['data_count'],
                        $functionLabel
                    ];
                    
                    // Add aggregated sensor values
                    foreach ($this->scheme->sensors as $sensor) {
                        $outputs = $sensor->num_of_outputs ?: 1;
                        $outputLabels = explode(',', $sensor->output_labels ?? '');
                        
                        // Perbaikan: cari sensor dengan id dan alias
                        $sensorData = null;
                        foreach ($row['sensors'] as $aggSensor) {
                            $aggId = $aggSensor['id'] ?? null;
                            $aggAlias = $aggSensor['alias'] ?? null;
                            $pivotAlias = $sensor->pivot->alias ?? null;
                            if ($aggId == $sensor->id && (string)$aggAlias === (string)$pivotAlias) {
                                $sensorData = $aggSensor;
                                break;
                            }
                        }
                        if ($sensorData && isset($sensorData['values'])) {
                            for ($i = 0; $i < $outputs; $i++) {
                                $label = isset($outputLabels[$i]) ? trim($outputLabels[$i]) : "Value " . ($i + 1);
                                $value = $sensorData['values'][$label] ?? '';
                                if (is_numeric($value)) {
                                    $value = number_format($value, 2);
                                }
                                $csvRow[] = $value;
                            }
                        } else {
                            for ($i = 0; $i < $outputs; $i++) {
                                $csvRow[] = '';
                            }
                        }
                    }
                }
                
                fputcsv($handle, $csvRow);
            }
            
            fclose($handle);
        };
        
        return Response::stream($callback, 200, $headers);
    }
    
    protected function exportToExcel($data, $filename, $exportingRawData = true)
    {
        return redirect()->route('export.excel', [
            'schemeId' => $this->schemeId,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'aggregation' => $this->dataAggregation,
            'aggregationFunction' => $this->aggregationFunction,
        ]);
    }
    
    public function render()
    {
        $scheme = $this->scheme; // Pastikan $scheme sudah di-load
        //$this->resetSelectedBarRecord(); // Reset selected bar record on render

        if ($scheme->visualization_type === 'bar') {
            return view('livewire.user.user-scheme-dashboard-bar', [
                'paginatedData' => $this->paginatedData,
                'processedData' => $this->processedData,
                'scheme' => $scheme,
            ])
            ->extends('layouts.windmill')
            ->section('content');
        } elseif ($scheme->visualization_type === 'none') {
            return view('livewire.user.user-scheme-dashboard-none', [
                'paginatedData' => $this->paginatedData,
                'processedData' => $this->processedData,
                'scheme' => $scheme,
            ])
            ->extends('layouts.windmill')
            ->section('content');
        } else {
            // Default: line chart
            return view('livewire.user.user-scheme-dashboard-line', [
                'paginatedData' => $this->paginatedData,
                'processedData' => $this->processedData,
                'scheme' => $scheme,
            ])
            ->extends('layouts.windmill')
            ->section('content');
        }
    }

    public function updatedAutoRefresh()
    {
        if ($this->autoRefresh) {
            $this->dispatch('start-auto-refresh');
        } else {
            $this->dispatch('stop-auto-refresh');
        }
        
        \Log::info('Auto refresh updated via checkbox', [
            'autoRefresh' => $this->autoRefresh,
            'refreshInterval' => $this->refreshInterval
        ]);
    }

    public function updatedRefreshInterval()
    {
        // Validate and clamp interval
        $oldInterval = $this->refreshInterval;
        $this->refreshInterval = max(5, min(300, $this->refreshInterval));
        
        if ($this->autoRefresh) {
            $this->dispatch('restart-auto-refresh', interval: $this->refreshInterval);
        }
        
        \Log::info('Refresh interval updated', [
            'old_interval' => $oldInterval,
            'new_interval' => $this->refreshInterval,
            'autoRefresh' => $this->autoRefresh,
            'url_params' => request()->query()
        ]);
        
        // Don't redirect or modify URL - let it stay in query string but don't let it affect logic
    }
    
    public function manualRefresh()
    {
        \Log::info('Manual refresh triggered', [
            'user_id' => Auth::id(),
            'scheme_id' => $this->schemeId,
            'aggregation_type' => $this->dataAggregation,
            'timestamp' => now()
        ]);
        
        // Call the main refresh function dengan context manual
        $this->refreshData('manual');
    }
    
    public function refreshBarChart($context = 'auto')
    {
        // Sama persis dengan refreshData, tapi hanya untuk bar chart
        $this->refreshData($context);
    }
    public function selectBarRecordById($id)
    {
        $index = null;
        foreach ($this->processedData as $i => $item) {
            // Untuk raw data, $item bisa object atau array
            $itemId = is_array($item) ? $item['id'] : $item->id;
            if ((string)$itemId === (string)$id) {
                $index = $i;
                break;
            }
        }
        $this->selectedBarRecord = $index;
        $this->dispatch('bar-record-selected', $index);
    }
    
    public function resetSelectedBarRecord()
    {
        // Selalu select latest (paling akhir) pada mode aktif
        $count = is_array($this->processedData) ? count($this->processedData) : null;
        $this->selectedBarRecord = $count > 0 ? $count - 1 : null;
        $this->dispatch('bar-record-selected', $this->selectedBarRecord);
    }
}