<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class SchemeSensor extends Model
{
    use CustomSoftDeletes;

    protected $fillable = [
        'scheme_id',
        'sensor_id',
        'order',
        'deleted'
    ];

    protected $casts = [
        'deleted' => 'boolean'
    ];

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }
}
