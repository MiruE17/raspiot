<?php

namespace App\Traits;

trait CustomSoftDeletes
{
    public function delete()
    {
        $this->deleted = true;
        return $this->save();
    }

    public function restore()
    {
        $this->deleted = false;
        return $this->save();
    }

    public function forceDelete()
    {
        return parent::delete();
    }

    public static function bootCustomSoftDeletes()
    {
        static::addGlobalScope('notDeleted', function ($builder) {
            $builder->where('deleted', false);
        });
    }

    public function scopeWithDeleted($query)
    {
        return $query->withoutGlobalScope('notDeleted');
    }

    public function scopeOnlyDeleted($query)
    {
        return $query->withoutGlobalScope('notDeleted')->where('deleted', true);
    }
}