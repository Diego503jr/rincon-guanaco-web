<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'destination_id',
        'activity_name',
        'notes',
        'people',
        'price',
        'duracion'
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    // Relación con los itinerarios donde se agregó esta actividad
    public function itineraries()
    {
        return $this->belongsToMany(Itinerary::class, 'itineraries_activities')
            ->withPivot('title', 'date_time')
            ->withTimestamps();
    }
}