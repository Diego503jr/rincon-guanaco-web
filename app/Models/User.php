<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, Notifiable;

    // role y status NO van aquí a propósito: así nadie puede
    // registrarse como admin mandando "role" en la petición.
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'traveler_type',
        'country_of_origin',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}
