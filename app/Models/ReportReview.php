<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportReview extends Model
{
    protected $fillable = [
        'user_id', 
        'review_id', 
        'nota', 
        'type_report', 
        'status'
    ];

    // Relación con el usuario que hizo el reporte
    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con la reseña que fue reportada
    public function review()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }
}