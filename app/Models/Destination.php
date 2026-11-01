<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'name',
        'category',
        'traveler_type',
        'description',
        'department',
        'municipality',
        'image_url',
        'latitude',
        'longitude',
        'status'
    ];

    // Relación: Un destino tiene muchos alojamientos
    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
    }

    // Relación: Un destino tiene muchas actividades
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}