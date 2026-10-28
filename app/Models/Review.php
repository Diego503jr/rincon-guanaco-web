<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id', 
        'destination_id', 
        'rating', 
        'comment', 
        'travel_date'
    ];

    // Relación con el usuario que escribió la reseña
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}