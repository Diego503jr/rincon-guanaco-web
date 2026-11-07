<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    protected $table = 'accommodations';

    protected $fillable = [
        'destination_id',
        'name',
        'type',
        'address',
        'price',
        'image_url',
        'contact_phone',
        'email',
        'status',
        'check_in',
        'check_out',
        'reserva',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    // Accessor para formatear automáticamente image_url a URL completa
    public function getImageUrlAttribute($value)
    {
        if ($value && !str_starts_with($value, 'http')) {
            return asset('storage/' . $value);
        }
        return $value;
    }

    // Relación: Un alojamiento pertenece a un destino
    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    //Relación con itinerario
    public function itineraries()
    {
        return $this->belongsToMany(Itinerary::class, 'itinerary_accommodations', 'accommodation_id', 'itinerary_id');
    }
}
