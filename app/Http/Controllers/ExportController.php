<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scheme;
use App\Models\DataIot;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController extends Controller
{
    public function exportExcel(Request $request)
    {
        $schemeId = $request->schemeId;
        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        
        // Verify user owns this scheme
        $scheme = Scheme::with('sensors')->findOrFail($schemeId);
        if ($scheme->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        $data = DataIot::where('scheme_id', $schemeId)
            ->whereBetween('created_at', [
                $dateFrom, 
                Carbon::parse($dateTo)->endOfDay()
            ])
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $col = 1;
        $sheet->setCellValue([$col++, 1], 'Timestamp');
        
        // Add sensor headers
        foreach ($scheme->sensors as $sensor) {
            $outputs = $sensor->num_of_outputs ?: 1;
            $outputLabels = explode(',', $sensor->output_labels ?? '');
            
            for ($i = 0; $i < $outputs; $i++) {
                $headerText = ($sensor->pivot->alias ?: $sensor->name);
                
                if ($outputs > 1) {
                    $headerText .= ' (' . (isset($outputLabels[$i]) ? $outputLabels[$i] : "Output ".($i+1)) . ')';
                }
                
                if ($sensor->unit) {
                    $headerText .= ' (' . $sensor->unit . ')';
                }
                
                $sheet->setCellValue([$col++, 1], $headerText);
            }
        }
        
        // Add additional columns headers
        if (is_array($scheme->additional_columns)) {
            foreach ($scheme->additional_columns as $column) {
                $sheet->setCellValue([$col++, 1], $column['name']);
            }
        }
        
        // Add data rows
        $row = 2;
        foreach ($data as $item) {
            $col = 1;
            
            // Add timestamp
            $sheet->setCellValue([$col++, $row], $item->created_at->format('Y-m-d H:i:s'));
            
            // Process the data
            $jsonData = $item->json_content;
            if (is_string($jsonData)) {
                $jsonData = json_decode($jsonData, true);
            }
            
            // Add sensor values
            foreach ($scheme->sensors as $sensor) {
                $outputs = $sensor->num_of_outputs ?: 1;
                
                // Find sensor data
                $sensorData = null;
                if (is_array($jsonData)) {
                    foreach ($jsonData as $sensorJson) {
                        if (isset($sensorJson['id']) && $sensorJson['id'] == $sensor->id) {
                            $sensorData = $sensorJson;
                            break;
                        }
                    }
                }
                
                // Add sensor values to row
                if ($sensorData && isset($sensorData['values'])) {
                    foreach ($sensorData['values'] as $value) {
                        $sheet->setCellValue([$col++, $row], $value['value']);
                    }
                } else {
                    // Add empty values if no data found
                    for ($i = 0; $i < $outputs; $i++) {
                        $sheet->setCellValue([$col++, $row], '');
                    }
                }
            }
            
            // Add additional columns
            if (is_array($scheme->additional_columns)) {
                foreach ($scheme->additional_columns as $column) {
                    $additionalData = $item->additional_content;
                    if (is_array($additionalData) && isset($additionalData[$column['name']])) {
                        $sheet->setCellValue([$col++, $row], $additionalData[$column['name']]);
                    } else {
                        $sheet->setCellValue([$col++, $row], '');
                    }
                }
            }
            
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Create file
        $filename = 'scheme_data_' . $scheme->name . '_' . date('Y-m-d') . '.xlsx';
        
        // Stream the file to the browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}