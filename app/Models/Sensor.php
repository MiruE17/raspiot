<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scheme;

class Sensor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'num_of_outputs',
        'output_labels',
        'picture',
        'deleted',
        'validation_settings',
    ];

    protected $casts = [
        'deleted' => 'boolean',
        'num_of_outputs' => 'integer',
        'validation_settings' => 'array',
    ];

    public function schemes(){
        return $this->belongsToMany(Scheme::class, 'scheme_sensors')->withPivot('order', 'deleted')->orderBy('pivot_order');
    }

    public function getOutputLabelsArrayAttribute(){
        return explode(',', $this->output_labels);
    }

    // Accessor untuk memastikan output_labels selalu string
    public function getOutputLabelsAttribute($value)
    {
        return $value ?: '';
    }

    // Scope query untuk hanya menampilkan sensor yang belum dihapus
    public function scopeActive($query)
    {
        return $query->where('deleted', false);
    }

    public function outputLabels()
    {
        // This converts the comma-separated string in the output_labels column
        // into a collection that behaves like a relationship
        $labels = explode(',', $this->output_labels ?: '');
        
        // Create a collection of label objects
        $collection = collect();
        foreach ($labels as $index => $label) {
            if (!empty(trim($label))) {
                $collection->push((object)[
                    'id' => $index + 1,
                    'sensor_id' => $this->id,
                    'label' => trim($label),
                    'position' => $index
                ]);
            }
        }
        
        return $collection;
    }
}
