<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'is_completed',
        'total_price'
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'total_price'  => 'decimal:2',
        ];
    }

    // Relación con el usuario dueño del itinerario
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con las actividades 
    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'itineraries_activities')
            ->withPivot('title', 'date_time')
            ->withTimestamps();
    }
}