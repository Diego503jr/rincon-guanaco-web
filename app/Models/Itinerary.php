<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
            'total_price' => 'decimal:2',
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

    // Actividades programadas del itinerario (filas de itineraries_activities con su id)
    public function items()
    {
        return $this->hasMany(ItineraryActivity::class, 'itinerary_id');
    }

        // Recalcula el total: (precio de cada actividad × personas) + alojamientos no cancelados
    public function recalculateTotal(): void
    {
        $activitiesTotal = $this->items()
            ->join('activities', 'activities.id', '=', 'itineraries_activities.activity_id')
            ->sum(DB::raw('activities.price * itineraries_activities.people'));

        // Se consulta con DB::table porque los alojamientos los maneja el Dev 3
        $accommodationsTotal = DB::table('itinerary_accommodations')
            ->where('itinerary_id', $this->id)
            ->where('status', '!=', 'cancelada')
            ->sum('total_price');

        $this->update(['total_price' => $activitiesTotal + $accommodationsTotal]);
    }
}