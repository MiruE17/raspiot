<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataIot;
use App\Models\Scheme;
use App\Models\ApiToken;
use App\Models\Sensor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DataIotApiController extends Controller
{
    /**
     * Receive IoT data from devices
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'scheme_id' => 'required|exists:schemes,id',
            'api_key' => 'required|string',
            'values' => 'required|string',
            'timestamp' => 'nullable|date',
            'additional_values' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify API key using ApiToken model
            $apiToken = ApiToken::findToken($request->api_key);
            
            if (!$apiToken || $apiToken->isExpired() || !$apiToken->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired API key'
                ], 401);
            }
            
            // Record token usage
            $apiToken->recordUsage();
            
            // Get user associated with the token
            $user = $apiToken->user;
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for this API key'
                ], 401);
            }
            
            // Get the scheme to verify it exists
            $scheme = Scheme::findOrFail($request->scheme_id);
            
            // Check if scheme belongs to the user
            if ($scheme->user_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scheme does not belong to authorized user'
                ], 403);
            }
            
            // Count values from request
            $values = explode(',', $request->values);

            // Calculate total expected outputs from all sensors in the scheme
            $totalExpectedValues = 0;
            $sensorConfigurations = [];

            // Use the scheme->sensors relation which includes the pivot table info
            $schemeSensors = $scheme->sensors()->withPivot('alias', 'order')->get();

            // Loop through each sensor in the scheme (including duplicates)
            foreach ($schemeSensors as $schemeSensor) {
                $numOutputs = $schemeSensor->num_of_outputs ?: 1; // Default to 1 if not specified
                $totalExpectedValues += $numOutputs;
                
                // Get output labels from sensor
                $outputLabels = explode(',', $schemeSensor->output_labels ?: '');
                
                // Use the alias from pivot if available, otherwise use sensor name
                $sensorName = $schemeSensor->pivot->alias ?: $schemeSensor->name;
                
                // Store sensor configuration for validation
                $sensorConfigurations[] = [
                    'name' => $sensorName,
                    'num_outputs' => $numOutputs,
                    'validation_settings' => is_string($schemeSensor->validation_settings) 
                        ? json_decode($schemeSensor->validation_settings, true) ?? []
                        : $schemeSensor->validation_settings ?? [],
                    'output_labels' => $outputLabels,
                ];
            }

            // Check if values count matches total expected outputs
            if (count($values) != $totalExpectedValues) {
                return response()->json([
                    'success' => false,
                    'message' => "Expected $totalExpectedValues values, got " . count($values) . " (based on sensor output configuration)"
                ], 422);
            }

            // Validate each value based on sensor configuration
            $valueIndex = 0;
            foreach ($sensorConfigurations as $sensorConfig) {
                for ($i = 0; $i < $sensorConfig['num_outputs']; $i++) {
                    $currentValue = $values[$valueIndex];
                    $validationSetting = $sensorConfig['validation_settings'][$i] ?? null;

                    if ($validationSetting) {
                        switch ($validationSetting['type']) {
                            case 'number':
                                if (!filter_var($currentValue, FILTER_VALIDATE_FLOAT)) {
                                    $columnName = $sensorConfig['output_labels'][$i] ?? "Output " . ($i + 1);
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Value for {$sensorConfig['name']} (column: {$columnName}) must be a valid number"
                                    ], 422);
                                }
                                
                                // Convert to float before comparison
                                $numericValue = (float)$currentValue;
                                
                                if (isset($validationSetting['min']) && $numericValue < (float)$validationSetting['min']) {
                                    $columnName = $sensorConfig['output_labels'][$i] ?? "Output " . ($i + 1);
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Value for {$sensorConfig['name']} (column: {$columnName}) must be at least {$validationSetting['min']}"
                                    ], 422);
                                }
                                if (isset($validationSetting['max']) && $numericValue > (float)$validationSetting['max']) {
                                    $columnName = $sensorConfig['output_labels'][$i] ?? "Output " . ($i + 1);
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Value for {$sensorConfig['name']} (column: {$columnName}) must not exceed {$validationSetting['max']}"
                                    ], 422);
                                }
                                break;

                            case 'string':
                                if (!is_string($currentValue) || strlen($currentValue) > 16) {
                                    $columnName = $sensorConfig['output_labels'][$i] ?? "Output " . ($i + 1);
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Value for {$sensorConfig['name']} (column: {$columnName}) must be a string with a maximum length of 16 characters"
                                    ], 422);
                                }
                                break;

                            case 'boolean':
                                if (!in_array($currentValue, [true, false, 'true', 'false', '0', '1', 0, 1], true)) {
                                    $columnName = $sensorConfig['output_labels'][$i] ?? "Output " . ($i + 1);
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Value for {$sensorConfig['name']} (column: {$columnName}) must be a boolean"
                                    ], 422);
                                }
                                break;

                            case 'percentage':
                                if (!is_numeric($currentValue) || $currentValue < $validationSetting['min'] || $currentValue > $validationSetting['max']) {
                                    // Convert min and max to percentage for display in error message
                                    $minPercent = (float)$validationSetting['min'] * 100;
                                    $maxPercent = (float)$validationSetting['max'] * 100;
                                    
                                    $columnName = $sensorConfig['output_labels'][$i] ?? "Output " . ($i + 1);
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Value for {$sensorConfig['name']} (column: {$columnName}) must be between {$minPercent}% and {$maxPercent}% (send as decimal between {$validationSetting['min']} and {$validationSetting['max']})"
                                    ], 422);
                                }
                                break;

                            default:
                                $columnName = $sensorConfig['output_labels'][$i] ?? "Output " . ($i + 1);
                                return response()->json([
                                    'success' => false,
                                    'message' => "Unsupported data type for {$sensorConfig['name']} (column: {$columnName})"
                                ], 422);
                        }
                    }

                    $valueIndex++;
                }
            }
            
            // Create new data record
            $dataIot = new DataIot();
            $dataIot->scheme_id = $request->scheme_id;
            $dataIot->user_id = $user->id;
            
            // Use dual write method to set both formats
            $dataIot->setContentDual($request->values, $scheme);
            
            // Validate additional values against column definitions if provided
            if ($request->has('additional_values') && !empty($scheme->additional_columns)) {
                $additionalValues = $request->additional_values;
                
                foreach ($scheme->additional_columns as $column) {
                    // Check if required columns are provided
                    if ($column['is_required'] && (!isset($additionalValues[$column['name']]) || $additionalValues[$column['name']] === '')) {
                        return response()->json([
                            'success' => false,
                            'message' => "Missing required additional value: {$column['name']}"
                        ], 422);
                    }
                    
                    // Validate value type if provided
                    if (isset($additionalValues[$column['name']])) {
                        $value = $additionalValues[$column['name']];
                        
                        switch ($column['data_type']) {
                            case 'number':
                                if (!is_numeric($value)) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Value for {$column['name']} must be a number"
                                    ], 422);
                                }
                                break;
                                
                            case 'boolean':
                                if (!is_bool($value) && !in_array($value, [true, false, 'true', 'false', '0', '1', 0, 1])) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Value for {$column['name']} must be a boolean"
                                    ], 422);
                                }
                                break;
                                
                            case 'date':
                                if (!strtotime($value)) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Value for {$column['name']} must be a valid date"
                                    ], 422);
                                }
                                break;
                        }
                    }
                }
                
                // Apply default values for missing columns
                $processedValues = [];
                foreach ($scheme->additional_columns as $column) {
                    $name = $column['name'];
                    
                    if (isset($additionalValues[$name])) {
                        $processedValues[$name] = $additionalValues[$name];
                    } elseif (!empty($column['default_value'])) {
                        $processedValues[$name] = $column['default_value'];
                    }
                }
                
                // Use processed values instead of raw input
                $dataIot->additional_content = $processedValues;
            }
            
            // Add additional values if provided
            if ($request->has('additional_values')) {
                $dataIot->additional_content = $request->additional_values;
            }
            
            // Use provided timestamp or current time
            $dataIot->created_at = $request->timestamp ?? now();
            
            $dataIot->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Data received successfully',
                'data_id' => $dataIot->id
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Failed to store IoT data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to store data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}