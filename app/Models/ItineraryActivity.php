<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Cada fila es una actividad programada dentro de un itinerario (tabla pivote con id propio)
class ItineraryActivity extends Model
{
    protected $table = 'itineraries_activities';

    protected $fillable = [
        'itinerary_id',
        'activity_id',
        'title',
        'date_time',
        'title',
        'people',
        'date_time'
    ];

    protected function casts(): array
    {
        return [
            'date_time' => 'datetime:Y-m-d H:i:s', // sin zona horaria, para que la app no la convierta
            'title',
            'people',
            'date_time'
        ];
    }

    // Relación con el itinerario al que pertenece
    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class, 'itinerary_id');
    }

    // Relación con la actividad del catálogo
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    
    // Subtotal = precio de la actividad × personas
    public function getSubtotalAttribute(): float
    {
        return round((float) ($this->activity->price ?? 0) * $this->people, 2);
    }
}