<?php

namespace App\Livewire\Admin;

use App\Models\Sensor;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads; // Tambahkan import ini
use Illuminate\Support\Facades\Storage;

class SensorManager extends Component
{
    use WithPagination;
    use WithFileUploads; // Tambahkan trait ini

    protected $paginationTheme = 'tailwind';

    public $sensorId;
    public $name;
    public $description;
    public $num_of_outputs = 1;
    public $output_labels = [''];
    public $picture; // Property untuk menyimpan file upload
    public $existingPicture;
    
    public $viewMode = false;
    public $selectedSensor = null;
    
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Add new property for deletion
    public $showDeleteModal = false;
    public $sensorToDelete = null;

    public $perPage = 5;

    protected $listeners = ['delete' => 'delete'];

    // Add these properties
    public $output_data_types = [];
    public $output_min_values = [];
    public $output_max_values = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'num_of_outputs' => 'required|integer|min:1',
            'output_labels' => 'required|array|min:1',
            'output_labels.*' => 'required|string|max:255',
            'picture' => 'nullable|image|max:1024', // max 1MB
        ];
    }

    public function mount()
    {
        $this->ensureOutputLabelsCount();
        $this->perPage = 5; // Show 5 items per page
        
        // Initialize data types with default 'number'
        $this->output_data_types = array_fill(0, count($this->output_labels), 'number');
        $this->output_min_values = array_fill(0, count($this->output_labels), null);
        $this->output_max_values = array_fill(0, count($this->output_labels), null);
    }

    public function updatedNumOfOutputs()
    {
        // Jika input kosong, reset output_labels menjadi hanya 1 elemen
        if (empty($this->num_of_outputs)) {
            // Simpan label pertama (jika ada)
            $firstLabel = $this->output_labels[0] ?? '';
            // Reset ke hanya 1 label
            $this->output_labels = [$firstLabel];
        } else {
            // Jika nilai < 1, paksa menjadi 1
            if ($this->num_of_outputs < 1) {
                $this->num_of_outputs = 1;
            }
            
            // Sesuaikan jumlah label berdasarkan nilai num_of_outputs
            $this->ensureOutputLabelsCount();
        }
        
        // Also ensure data types, min, and max values match the number of outputs
        $this->ensureValidationFieldsCount();
    }

    /**
     * Menghasilkan label default untuk output berdasarkan index
     *
     * @param int $index
     * @return string
     */
    private function getDefaultLabel($index)
    {
        return "val_" . ($index + 1);
    }
    
    /**
     * Memastikan setiap label yang kosong diganti dengan nilai default saat submit
     */
    private function applyDefaultLabels()
    {
        foreach ($this->output_labels as $index => $label) {
            if (trim($label) === '') {
                $this->output_labels[$index] = $this->getDefaultLabel($index);
            }
        }
    }

    private function ensureOutputLabelsCount()
    {
        // Jika num_of_outputs kosong, biarkan output_labels sesuai kondisi terakhir
        if (empty($this->num_of_outputs)) {
            return;
        }
        
        // Ensure minimum value for calculation only
        $outputCount = max(1, $this->num_of_outputs);
        
        // Get the current count of output labels
        $currentCount = count($this->output_labels);
        
        if ($outputCount > $currentCount) {
            // Add empty labels if the number of outputs increases
            for ($i = $currentCount; $i < $outputCount; $i++) {
                $this->output_labels[] = '';
            }
        } elseif ($outputCount < $currentCount) {
            // Trim the array if the number of outputs decreases, but keep at least one
            $this->output_labels = array_slice($this->output_labels, 0, $outputCount);
        }
        
        // Final check to ensure we always have at least one output label
        if (empty($this->output_labels)) {
            $this->output_labels = [''];
        }
    }

    // Add a new method to ensure validation fields count
    private function ensureValidationFieldsCount()
    {
        $outputCount = max(1, $this->num_of_outputs);
        
        // Ensure data types array has the right size
        while (count($this->output_data_types) < $outputCount) {
            $this->output_data_types[] = 'string';
        }
        if (count($this->output_data_types) > $outputCount) {
            $this->output_data_types = array_slice($this->output_data_types, 0, $outputCount);
        }
        
        // Ensure min values array has the right size
        while (count($this->output_min_values) < $outputCount) {
            $this->output_min_values[] = null;
        }
        if (count($this->output_min_values) > $outputCount) {
            $this->output_min_values = array_slice($this->output_min_values, 0, $outputCount);
        }
        
        // Ensure max values array has the right size
        while (count($this->output_max_values) < $outputCount) {
            $this->output_max_values[] = null;
        }
        if (count($this->output_max_values) > $outputCount) {
            $this->output_max_values = array_slice($this->output_max_values, 0, $outputCount);
        }
    }

    public function render()
    {
        $sensors = Sensor::where('deleted', false)
            ->where(function ($query) {
                $query->where('name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.admin-sensor-manager', compact('sensors'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->viewMode = false;
        $this->dispatch('show-modal');
    }

    /**
     * Validates that all output labels are unique within the sensor
     * 
     * @return bool
     */
    private function validateUniqueLabels()
    {
        // Hanya lakukan validasi jika ada label
        if (empty($this->output_labels)) {
            return true;
        }

        // Filter out empty values
        $nonEmptyLabels = array_filter($this->output_labels, function($label) {
            return trim($label) !== '';
        });
        
        // Get unique values (case-insensitive)
        $uniqueLabels = [];
        $duplicates = [];
        
        foreach ($nonEmptyLabels as $index => $label) {
            $lowerLabel = strtolower(trim($label));
            
            if (isset($uniqueLabels[$lowerLabel])) {
                $duplicates[] = $label;
            } else {
                $uniqueLabels[$lowerLabel] = $index;
            }
        }
        
        // If we found duplicates, add validation error
        if (!empty($duplicates)) {
            $this->addError('output_labels', 'Output labels must be unique. Duplicate labels: ' . implode(', ', array_unique($duplicates)));
            return false;
        }
        
        return true;
    }

    public function store()
    {
        // Validasi input
        $this->validate();
        
        // Pastikan num_of_outputs memiliki nilai minimal 1
        if (empty($this->num_of_outputs)) {
            $this->num_of_outputs = 1;
        }
        
        // Pastikan jumlah output_labels sesuai dengan num_of_outputs
        $this->ensureOutputLabelsCount();
        
        // Terapkan default labels untuk field yang kosong
        $this->applyDefaultLabels();
        
        // Tentukan nilai picture yang akan disimpan
        $picturePath = $this->existingPicture; // Default ke gambar yang sudah ada
        
        // Jika ada upload file baru
        if ($this->picture) {
            // Hapus file lama jika ada
            if ($this->sensorId && $this->existingPicture) {
                Storage::disk('public')->delete($this->existingPicture);
            }
            
            // Simpan file baru
            $picturePath = $this->picture->store('sensors', 'public');
        }
        
        // Prepare validation settings
        $validationSettings = [];
        for ($i = 0; $i < $this->num_of_outputs; $i++) {
            $type = $this->output_data_types[$i] ?? 'string';
            $validationSettings[] = [
                'type' => $type,
                'min' => in_array($type, ['number', 'percentage']) ? $this->output_min_values[$i] : null,
                'max' => in_array($type, ['number', 'percentage']) ? $this->output_max_values[$i] : ($type === 'string' ? 16 : null),
            ];
        }
        
        // Add validation_settings to sensor data
        $sensorData = [
            'name' => $this->name,
            'description' => $this->description,
            'num_of_outputs' => $this->num_of_outputs,
            'output_labels' => implode(',', $this->output_labels),
            'picture' => $picturePath,
            'validation_settings' => json_encode($validationSettings),
        ];
        
        if ($this->sensorId) {
            // Update logic
            $sensor = Sensor::find($this->sensorId);
            $sensor->update($sensorData);
            session()->flash('message', 'Sensor updated successfully.');
        } else {
            // Create logic
            $sensorData['deleted'] = false;
            Sensor::create($sensorData);
            session()->flash('message', 'Sensor created successfully.');
        }
        
        $this->resetInputFields();
        $this->dispatch('hide-modal');
    }

    public function edit($id)
    {
        $this->resetInputFields();
        $this->viewMode = false;
        $this->sensorId = $id;
        
        $sensor = Sensor::findOrFail($id);
        $this->name = $sensor->name;
        $this->description = $sensor->description;
        $this->num_of_outputs = $sensor->num_of_outputs;
        $this->output_labels = explode(',', $sensor->output_labels);
        $this->existingPicture = $sensor->picture; // Simpan path gambar saat ini
        
        // Load validation settings if they exist
        if ($sensor->validation_settings) {
            $validationSettings = json_decode($sensor->validation_settings, true);
            foreach ($validationSettings as $index => $setting) {
                if (isset($this->output_data_types[$index])) {
                    $this->output_data_types[$index] = $setting['type'] ?? 'string';
                    $this->output_min_values[$index] = $setting['min'] ?? null;
                    $this->output_max_values[$index] = $setting['max'] ?? null;
                }
            }
        }
        
        // Ensure we have the correct number of labels and validation fields
        $this->ensureOutputLabelsCount();
        $this->ensureValidationFieldsCount();
        
        $this->dispatch('show-modal');
    }

    public function view($id)
    {
        $this->viewMode = true;
        $this->selectedSensor = Sensor::findOrFail($id);
        $this->dispatch('show-modal');
    }

    // Modified confirmation method
    public function confirmDelete($id)
    {
        $this->sensorToDelete = $id;
        $this->showDeleteModal = true;
    }

    // Delete method stays mostly the same
    public function delete()
    {
        $id = $this->sensorToDelete;
        $sensor = Sensor::find($id);
        
        if (!$sensor) {
            session()->flash('error', 'Sensor not found.');
            return;
        }
        
        // Soft delete dengan mengubah flag deleted
        $sensor->update(['deleted' => true]);
        
        session()->flash('message', 'Sensor deleted successfully.');
        
        // Hide modal
        $this->showDeleteModal = false;
        $this->sensorToDelete = null;
    }

    // Cancel delete modal
    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->sensorToDelete = null;
    }

    private function resetInputFields()
    {
        $this->sensorId = null;
        $this->name = '';
        $this->description = '';
        $this->num_of_outputs = 1; // Always reset to 1
        $this->output_labels = [''];
        $this->output_data_types = ['string'];
        $this->output_min_values = [null];
        $this->output_max_values = [null];
        $this->picture = null;
        $this->existingPicture = null;
        $this->selectedSensor = null;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function removePicture()
    {
        if ($this->existingPicture) {
            Storage::disk('public')->delete($this->existingPicture);
            
            if ($this->sensorId) {
                $sensor = Sensor::find($this->sensorId);
                $sensor->update(['picture' => null]);
            }
            
            $this->existingPicture = null;
        }
        
        // Reset file input
        $this->picture = null;
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }
}