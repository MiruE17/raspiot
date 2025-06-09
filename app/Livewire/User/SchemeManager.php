<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\User\SchemeDashboard;
use App\Models\Scheme;
use App\Models\Sensor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SchemeManager extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Form properties
    public $schemeId = null;
    public $name = '';
    public $description = '';
    public $visualizationType = 'line';
    public $selectedSensors = [];
    public $sensorOrders = [];
    public $newSensorId = null; // Tambahkan property untuk sensor baru
    public $pendingSensors = [null]; // Ubah dari pendingSensor (tunggal) menjadi array
    public $additionalColumns = []; // Add property for additional columns
    public $sensorAliases = ['']; // Add a new property for aliases
    
    // Modal states
    public $viewMode = false;
    public $showDeleteModal = false;
    public $selectedScheme = null;
    
    protected $listeners = ['refreshSchemes' => '$refresh'];
    
    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'visualizationType' => ['required', Rule::in(['line', 'bar', 'scatter', 'pie', 'gauge', 'none'])],
            'selectedSensors' => ['required', 'array', 'min:1'],
            'selectedSensors.*' => ['required', 'exists:sensors,id'],
            'sensorOrders.*' => ['integer', 'min:0'],
            'additionalColumns' => ['nullable', 'array'],
            'additionalColumns.*.name' => ['required', 'string', 'max:255'],
            'additionalColumns.*.data_type' => ['required', 'string', 'in:string,number,boolean,date'],
            'additionalColumns.*.default_value' => ['nullable', 'string'],
            'additionalColumns.*.is_required' => ['boolean'],
        ];
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function create()
    {
        $this->resetForm();
        $this->viewMode = false;
        $this->dispatch('show-modal');
    }
    
    public function view($id)
    {
        $this->schemeId = $id;
        $this->viewMode = true;
        
        // Load scheme dengan relasi sensors dan pivot data yang benar
        $this->selectedScheme = Scheme::with(['sensors' => function($query) {
            $query->withPivot('alias', 'order')->orderBy('scheme_sensors.order');
        }])->findOrFail($id);
        
        // Load basic data
        $this->name = $this->selectedScheme->name;
        $this->description = $this->selectedScheme->description;
        $this->visualizationType = $this->selectedScheme->visualization_type;
        $this->additionalColumns = $this->selectedScheme->additional_columns ?: [];
        
        $this->dispatch('show-modal');
    }
    
    public function edit($schemeId)
    {
        $this->resetErrorBag();
        $this->viewMode = false;
        $this->schemeId = $schemeId;
        
        // Load scheme dengan pivot data yang benar
        $scheme = Scheme::with(['sensors' => function($query) {
            $query->withPivot('alias', 'order')->orderBy('scheme_sensors.order');
        }])->findOrFail($schemeId);
        
        // Set selectedScheme untuk view compatibility
        $this->selectedScheme = $scheme;
        
        // Basic fields
        $this->name = $scheme->name;
        $this->description = $scheme->description;
        $this->visualizationType = $scheme->visualization_type;
        $this->additionalColumns = $scheme->additional_columns ?: [];
        
        // Clear arrays
        $this->pendingSensors = [];
        $this->selectedSensors = []; 
        $this->sensorAliases = []; 
        
        // Load sensors dengan pivot data
        foreach($scheme->sensors as $sensor) {
            $this->pendingSensors[] = $sensor->id;
            $this->selectedSensors[] = $sensor->id;
            $this->sensorAliases[] = (string)($sensor->pivot->alias ?: '');
        }
        
        // Ensure minimal dropdown
        if(empty($this->pendingSensors)) {
            $this->pendingSensors[] = '';
            $this->sensorAliases[] = '';
        }
        
        $this->dispatch('show-modal');
    }
    
    public function store()
    {
        // Transfer values from pendingSensors to selectedSensors BEFORE validation
        $this->selectedSensors = array_values(array_filter($this->pendingSensors));
        
        // Fix: Ensure we only keep aliases for the sensors we're actually keeping
        $filteredAliases = [];
        foreach ($this->pendingSensors as $index => $sensorId) {
            if (!empty($sensorId)) {
                $filteredAliases[] = $this->sensorAliases[$index] ?? '';
            }
        }
        $this->sensorAliases = $filteredAliases;
        
        // Then validate
        $this->validate();
        
        // Create or update scheme
        $scheme = $this->schemeId
            ? Scheme::findOrFail($this->schemeId)
            : new Scheme();
            
        $scheme->name = $this->name;
        $scheme->description = $this->description;
        $scheme->visualization_type = $this->visualizationType;
        $scheme->additional_columns = !empty($this->additionalColumns) ? $this->additionalColumns : null;
        
        if (!$this->schemeId) {
            $scheme->user_id = auth()->id();
        }
        
        $scheme->save();
        
        // When syncing sensors, use a collection to preserve order
        $syncData = [];
        $sensorCounts = array_count_values($this->selectedSensors);

        foreach ($this->selectedSensors as $order => $sensorId) {
            $alias = $this->sensorAliases[$order] ?? ''; // Include the alias
            
            // Check if this is a duplicate sensor ID that we've seen before
            if ($sensorCounts[$sensorId] > 1) {
                // If we've already added this sensor before, use a unique key
                if (isset($syncData[$sensorId])) {
                    $uniqueKey = $sensorId . '_' . uniqid();
                    $syncData[$uniqueKey] = [
                        'order' => $order + 1, 
                        'sensor_id' => $sensorId,
                        'alias' => $alias, // Include the alias
                    ];
                } else {
                    // First occurrence of a sensor that will appear multiple times
                    $syncData[$sensorId] = [
                        'order' => $order + 1,
                        'alias' => $alias, // Include the alias
                    ];
                }
            } else {
                // Sensor appears only once, no special handling needed
                $syncData[$sensorId] = [
                    'order' => $order + 1,
                    'alias' => $alias, // Include the alias
                ];
            }
        }
        
        // Clear existing relationships first to ensure clean sync
        $scheme->sensors()->detach();
        
        // Then sync with the new data
        if (!empty($syncData)) {
            foreach ($syncData as $sensorId => $attributes) {
                // If this is a unique key (contains underscore), extract the real sensor ID
                if (strpos($sensorId, '_') !== false) {
                    $realSensorId = $attributes['sensor_id'];
                    $scheme->sensors()->attach($realSensorId, ['order' => $attributes['order'], 'alias' => $attributes['alias']]);
                } else {
                    // Regular case
                    $scheme->sensors()->attach($sensorId, ['order' => $attributes['order'], 'alias' => $attributes['alias']]);
                }
            }
        }
        
        $this->dispatch('hide-modal');
        $this->resetForm();

        $this->dispatch('toast', [
            'message' => $this->schemeId ? 'The scheme has been updated successfully.' : 'The scheme has been created successfully.',
            'type' => 'success'
        ]);
    }
    
    public function confirmDelete($id)
    {
        $this->schemeId = $id;
        $this->showDeleteModal = true;
    }
    
    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->schemeId = null;
    }
    
    public function delete()
    {
        try {
            $scheme = Scheme::findOrFail($this->schemeId);
            $scheme->deleted = true;
            $scheme->save();
            
            $this->showDeleteModal = false;
            $this->schemeId = null; // Reset ID after deletion
            
            // Dispatch the toast notification event
            $this->dispatch('toast', [
                'message' => 'Scheme deleted successfully.',
                'type' => 'success'
            ]);
            
            // Reset the page to ensure removed item isn't causing issues
            $this->resetPage();
        } catch (\Exception $e) {
            $this->showDeleteModal = false;
            $this->dispatch('toast', [
                'message' => 'Error deleting scheme: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }
    
    public function resetForm()
    {
        $this->schemeId = null;
        $this->name = '';
        $this->description = '';
        $this->visualizationType = 'line';
        $this->selectedSensors = [];
        $this->sensorOrders = [];
        $this->pendingSensors = [null]; // Reset ke satu dropdown kosong
        $this->additionalColumns = []; // Reset additional columns
        $this->sensorAliases = ['']; // Reset the aliases array
        $this->selectedScheme = null;
        $this->viewMode = false;
    }
    
    // Perbaikan method addSensor
    public function addSensor($index)
    {
        // Validasi input
        if (!isset($this->pendingSensors[$index]) || !$this->pendingSensors[$index]) {
            return;
        }
        
        $sensorId = $this->pendingSensors[$index];
        
        // Ensure sensorId is a valid non-null value
        if ($sensorId === null || (!is_int($sensorId) && !is_string($sensorId))) {
            return;
        }
        
        // Cek apakah sensor sudah ada di daftar
        if (!in_array($sensorId, $this->selectedSensors)) {
            // Tambahkan ke daftar sensor yang dipilih
            $this->selectedSensors[] = $sensorId;
            
            // Set order berdasarkan urutan dalam array (urutan penambahan)
            $this->sensorOrders[$sensorId] = count($this->selectedSensors) - 1;
        }
        
        // Reset dropdown yang digunakan
        $this->pendingSensors[$index] = null;
    }

    // Method untuk menambah dropdown baru
    public function addSensorDropdown()
    {
        // Tambahkan dropdown kosong baru
        $this->pendingSensors[] = null;
        $this->sensorAliases[] = ''; // Add an empty alias entry
    }

    public function removeSensor($index)
    {
        // Ambil ID sensor yang akan dihapus
        $sensorId = $this->selectedSensors[$index];
        
        // Hapus dari array selectedSensors
        unset($this->selectedSensors[$index]);
        
        // Re-index array untuk menghindari lubang
        $this->selectedSensors = array_values($this->selectedSensors);
        
        // Hapus order jika ada
        if (isset($this->sensorOrders[$sensorId])) {
            unset($this->sensorOrders[$sensorId]);
        }
    }

    public function addAdditionalColumn()
    {
        $this->additionalColumns[] = [
            'name' => '',
            'data_type' => 'string',
            'default_value' => '',
            'is_required' => false
        ];
    }

    public function removeAdditionalColumn($index)
    {
        unset($this->additionalColumns[$index]);
        $this->additionalColumns = array_values($this->additionalColumns);
    }

    public function mount()
    {
        // Kode existing mount()
        // ...
        
        // Inisialisasi pendingSensors jika belum ada
        if (empty($this->pendingSensors)) {
            $this->pendingSensors = [null];
        }
    }

    public function render()
    {
        $schemes = Scheme::where('user_id', Auth::id())
            ->where('deleted', false)
            ->when($this->search, function ($query) {
                return $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
            
        // Load sensors tanpa field unit yang tidak ada
        $sensors = Sensor::where('deleted', false)
            ->select('id', 'name', 'description', 'num_of_outputs', 'output_labels', 'picture', 'validation_settings')
            ->orderBy('name')
            ->get();
        
        $additionalCount = 0;
        if ($this->selectedScheme && is_array($this->selectedScheme->additional_columns)) {
            $additionalCount = count($this->selectedScheme->additional_columns);
        }
        
        return view('livewire.user.user-scheme-manager', [
            'schemes' => $schemes,
            'sensors' => $sensors,
            'additionalCount' => $additionalCount,
        ]);
    }

    // Add this new method
    public function removePendingSensor($index)
    {
        // Remove the sensor and its alias
        unset($this->pendingSensors[$index]);
        unset($this->sensorAliases[$index]);
        
        // Re-index arrays to avoid gaps
        $this->pendingSensors = array_values($this->pendingSensors);
        $this->sensorAliases = array_values($this->sensorAliases);
        
        // Ensure we always have at least one dropdown
        if (empty($this->pendingSensors)) {
            $this->pendingSensors[] = '';
            $this->sensorAliases[] = '';
        }
    }

    public function show()
    {
        return SchemeDashboard::class;
    }

    // Tambahkan method untuk mendapatkan informasi sensor
    public function getSensorInfo($sensorId)
    {
        if (!$sensorId) return null;
        
        $sensor = Sensor::find($sensorId);
        if (!$sensor) return null;
        
        return [
            'id' => $sensor->id,
            'name' => $sensor->name,
            'description' => $sensor->description,
            'unit' => $sensor->unit,
            'num_of_outputs' => $sensor->num_of_outputs ?? 1,
            'output_labels' => explode(',', $sensor->output_labels ?? ''),
            'picture' => $sensor->picture,
            'validation_settings' => $sensor->validation_settings
        ];
    }
}
