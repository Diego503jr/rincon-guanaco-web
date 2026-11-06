<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Cada fila es un alojamiento reservado dentro de un itinerario (modal Reservar Alojamiento de la app)
class ItineraryAccommodation extends Model
{
    protected $table = 'itinerary_accommodations';

    protected $fillable = [
        'itinerary_id',
        'accommodation_id',
        'guests',
        'total_price',
        'status',
        'check_in',
        'check_out',
        'is_completed'
    ];

    protected function casts(): array
    {
        return [
            'check_in'     => 'datetime:Y-m-d H:i:s', // sin zona horaria, para que la app no la convierta
            'check_out'    => 'datetime:Y-m-d H:i:s',
            'guests'       => 'integer',
            'total_price'  => 'decimal:2',
            'is_completed' => 'boolean',
        ];
    }

    // Relación con el itinerario al que pertenece
    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class, 'itinerary_id');
    }

    // Relación con el alojamiento del catálogo
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class, 'accommodation_id');
    }

    // Noches = días entre la fecha de entrada y la de salida
    public function getNightsAttribute(): int
    {
        return (int) $this->check_in->copy()->startOfDay()->diffInDays($this->check_out->copy()->startOfDay());
    }
}