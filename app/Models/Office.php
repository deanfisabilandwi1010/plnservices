<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $guarded = ['id'];

    /** Shape consumed by resources/js/site/map.js */
    public function toMapArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'address' => $this->address,
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
        ];
    }
}
