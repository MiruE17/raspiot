<!-- filepath: c:\Users\Aji\Documents\raspiot\app\Livewire\Visualization\TimeseriesChart.php -->
<?php

namespace App\Livewire\Visualization;

use App\Models\Scheme;
use App\Models\DataIot; // Ganti Reading dengan DataIot
use Livewire\Component;
use Carbon\Carbon;

class TimeseriesChart extends Component
{
    public $schemeId;
    public $scheme;
    public $timeRange = '24h'; // Default: 24 jam terakhir
    public $chartData = [];
    
    public function mount($schemeId)
    {
        $this->schemeId = $schemeId;
        $this->scheme = Scheme::with('sensor')->findOrFail($schemeId);
        $this->loadData();
    }
    
    public function loadData()
    {
        // Tentukan rentang waktu berdasarkan timeRange
        $endDate = Carbon::now();
        $startDate = match($this->timeRange) {
            '1h' => Carbon::now()->subHour(),
            '6h' => Carbon::now()->subHours(6),
            '24h' => Carbon::now()->subDay(),
            '7d' => Carbon::now()->subDays(7),
            '30d' => Carbon::now()->subDays(30),
            default => Carbon::now()->subDay(),
        };
        
        // Ambil data DataIot untuk scheme ini
        $dataPoints = DataIot::where('scheme_id', $this->schemeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();
        
        // Format data untuk chart
        $labels = [];
        $datasets = [];
        
        // Inisialisasi array untuk setiap output
        $outputCount = $this->scheme->sensor->num_of_outputs ?? 1;
        $outputs = [];
        for ($i = 0; $i < $outputCount; $i++) {
            $outputs[$i] = [];
        }
        
        // Grup readings berdasarkan waktu
        foreach ($dataPoints as $data) {
            $timestamp = $data->created_at->format('Y-m-d H:i:s');
            if (!in_array($timestamp, $labels)) {
                $labels[] = $timestamp;
            }
            
            // Sesuaikan field yang digunakan (value atau content)
            $values = json_decode($data->content ?? $data->value ?? '[]', true);
            if (is_array($values)) {
                foreach ($values as $index => $value) {
                    if (isset($outputs[$index])) {
                        $outputs[$index][$timestamp] = (float) $value;
                    }
                }
            }
        }
        
        // Buat dataset untuk setiap output
        $outputLabels = explode(',', $this->scheme->sensor->output_labels ?? '');
        $colors = ['#4C51BF', '#38B2AC', '#ED8936', '#E53E3E', '#805AD5', '#3182CE', '#38A169'];
        
        foreach ($outputs as $index => $data) {
            $dataPoints = [];
            foreach ($labels as $label) {
                $dataPoints[] = $data[$label] ?? null;
            }
            
            $datasets[] = [
                'label' => $outputLabels[$index] ?? "Output " . ($index + 1),
                'data' => $dataPoints,
                'borderColor' => $colors[$index % count($colors)],
                'backgroundColor' => $colors[$index % count($colors)] . '20',
                'fill' => false,
                'tension' => 0.1
            ];
        }
        
        $this->chartData = [
            'labels' => $labels,
            'datasets' => $datasets
        ];
        
        $this->dispatch('chartDataUpdated', [
            'data' => $this->chartData,
            'timeRange' => $this->timeRange
        ]);
    }
    
    public function changeTimeRange($range)
    {
        $this->timeRange = $range;
        $this->loadData();
    }
    
    public function render()
    {
        return view('livewire.visualization.timeseries-chart');
    }
}