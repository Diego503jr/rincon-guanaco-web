<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\alert;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'email'             => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'phone'             => ['nullable', 'string', 'max:50'],
            'traveler_type'     => ['nullable', 'in:mochilero,familia,pareja'],
            'country_of_origin' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create($data)->fresh(); // fresh() trae role/status por defecto de la BD

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $user->createToken('mobile')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if ($user->status !== 'activo') {
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta está inactiva.'],
            ]);
        }

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $user->createToken('mobile')->plainTextToken,
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        $user = User::where('email', $request->email)->first();

        // Un solo mensaje para todos los casos: no revela si el correo existe
        if (
            ! $user
            || ! Hash::check($request->password, $user->password)
            || $user->role !== 'admin'
            || $user->status !== 'activo'
        ) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas o sin acceso al panel.'],
            ]);
        }

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $user->createToken('admin-web')->plainTextToken,
        ]);
    }
}
