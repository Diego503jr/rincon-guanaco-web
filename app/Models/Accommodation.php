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

    // Relación: Un alojamiento pertenece a un destino
    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }
}