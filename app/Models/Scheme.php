<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;
use Illuminate\Support\Facades\Log;

class Scheme extends Model
{
    use HasUuids, CustomSoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'visualization_type',
        'visualization_settings',
        'deleted',
        'additional_columns'
    ];

    protected $casts = [
        'deleted' => 'boolean',
        'visualization_settings' => 'array',
        'additional_columns' => 'array',
    ];
    public $incrementing = false;
    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sensors()
    {
        return $this->belongsToMany(Sensor::class, 'scheme_sensors')
                    ->withPivot('order', 'alias')
                    ->orderBy('scheme_sensors.order');
    }

    public function data()
    {
        return $this->hasMany(DataIot::class);
    }

    // Alias for data relationship to maintain compatibility
    public function dataIots()
    {
        return $this->data();
    }

    public function getColumnsArrayAttribute()
    {
        return $this->sensors()
                ->orderBy('scheme_sensors.order')
                ->pluck('name')
                ->toArray();
    }

    public function updateColumnsFromSensors()
    {
        // Ambil nama sensor secara berurutan berdasarkan pivot 'order'
        $columns = $this->sensors()
            ->orderBy('scheme_sensors.order')
            ->pluck('name')
            ->toArray();

        // Misalnya, simpan dalam atribut visualisasi_settings atau kolom lain sesuai kebutuhan
        $this->update([
            'visualization_settings' => $columns,
        ]);
    }
}
