<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// Perfil del usuario de la app móvil (pantalla "Mi Perfil")
class ProfileController extends Controller
{
    // Editar datos personales: PUT /api/me
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'email'             => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'             => ['nullable', 'string', 'max:50'],
            'traveler_type'     => ['nullable', 'in:mochilero,familia,pareja'],
            'country_of_origin' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required'    => 'El nombre es obligatorio.',
            'email.required'   => 'El correo es obligatorio.',
            'email.email'      => 'Ingresa un correo válido.',
            'email.unique'     => 'Ese correo ya está registrado por otro usuario.',
            'traveler_type.in' => 'El tipo de viajero no es válido.',
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Perfil actualizado.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    // Cambiar contraseña: PUT /api/me/password
    public function password(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Ingresa tu contraseña actual.',
            'password.required'  => 'Ingresa la contraseña nueva.',
            'password.confirmed' => 'Las contraseñas nuevas no coinciden.',
            'password.min'       => 'La contraseña nueva debe tener al menos 8 caracteres.',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        $user->update(['password' => $request->password]); // el modelo la encripta (cast 'hashed')

        // Cierra la sesión en los demás dispositivos (deja activa la actual)
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json(['message' => 'Contraseña actualizada.']);
    }
}