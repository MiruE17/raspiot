<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class DataIot extends Model
{
    //use HasFactory;
    use CustomSoftDeletes;
    protected $table = 'data_iots';
    protected $fillable = [
        'content',
        'json_content',
        'additional_content',
        'scheme_id',
        'user_id',
        'deleted'
    ];

    protected $casts = [
        'deleted' => 'boolean',
        'json_content' => 'array',
        'additional_content' => 'array',
    ];

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set content in both CSV and JSON format (dual write)
     * 
     * @param string $csvContent The CSV content
     * @param Scheme $scheme The scheme to use for JSON formatting
     * @return $this
     */
    public function setContentDual($rawValuesString, $scheme)
    {
        // Store the raw CSV data
        $this->content = $rawValuesString;
        
        // Parse into JSON format with labels
        $values = explode(',', $rawValuesString);
        $jsonData = [];
        $valuesIndex = 0;
        
        foreach ($scheme->sensors()->orderBy('scheme_sensors.order')->get() as $sensor) {
            $sensorData = [
                'id' => $sensor->id,
                'name' => $sensor->name,
                'alias' => $sensor->pivot->alias ?: null,
                'values' => []
            ];
            
            // Handle output labels
            $outputLabels = $sensor->outputLabels(); // This is now a Collection, not a relationship
            $numOutputs = $sensor->num_of_outputs ?: 1;
            
            for ($i = 0; $i < $numOutputs; $i++) {
                if ($valuesIndex < count($values)) {
                    // Get label for this position if available
                    $label = null;
                    foreach ($outputLabels as $outputLabel) {
                        if ($outputLabel->position == $i) {
                            $label = $outputLabel->label;
                            break;
                        }
                    }
                    
                    $sensorData['values'][] = [
                        'label' => $label ?: "Value " . ($i + 1),
                        'value' => trim($values[$valuesIndex])
                    ];
                    
                    $valuesIndex++;
                }
            }
            
            $jsonData[] = $sensorData;
        }
        
        $this->json_content = json_encode($jsonData);
        
        return $this;
    }

    public function getContentArrayAttribute()
    {
        return array_map('trim', explode(',', $this->content));
    }

    public function validateContent(){
        $contentArray = $this->getContentArrayAttribute();
        return count($contentArray) === $this->scheme->num_of_col;
    }

    /**
     * Get formatted content (prioritize JSON if available)
     */
    public function getFormattedContentAttribute(){
        // If json_content is available, use it
        if (!empty($this->json_content)) {
            return $this->json_content;
        }
        
        // Otherwise fall back to old CSV method
        $values = $this->getContentArrayAttribute();
        $columns = $this->scheme->getColumnsArrayAttribute();

        return array_combine($columns, $values);
    }

    /**
     * Parse the content string into array of values
     */
    public function getValuesAttribute()
    {
        if (empty($this->content)) {
            return [];
        }
        
        return explode(',', $this->content);
    }

    /**
     * Scope a query to only include data for a specific scheme
     */
    public function scopeForScheme($query, $schemeId)
    {
        return $query->where('scheme_id', $schemeId)
                     ->where('deleted', false);
    }
    
    /**
     * Scope a query to only include recent data
     */
    public function scopeRecent($query, $limit = 100)
    {
        return $query->where('deleted', false)
                     ->latest('created_at')
                     ->limit($limit);
    }
    
    /**
     * Scope for time range filtering
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->where('created_at', '>=', $startDate)
                     ->where('created_at', '<=', $endDate);
    }
}
