<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role, fn($q, $role) => $q->where('role', $role))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->paginate(10);

        return response()->json($users);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $user = User::create($request->validate($this->rules()));

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'user'    => $user,
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate($this->rules($user));

        if (empty($data['password'])) {
            unset($data['password']); // vacío = conservar la actual
        }

        if (
            $user->id === $request->user()->id
            && (($data['role'] ?? $user->role) !== $user->role
                || ($data['status'] ?? $user->status) !== $user->status)
        ) {
            return response()->json([
                'message' => 'No puedes cambiar tu propio rol o estado.',
            ], 422);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'user'    => $user,
        ]);
    }

    // Bloquear / activar
    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', Rule::in(['activo', 'inactivo'])],
        ]);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'No puedes bloquear tu propia cuenta.'], 422);
        }

        $user->update(['status' => $request->status]);

        // Si se bloquea, se cierran sus sesiones al instante
        if ($request->status === 'inactivo') {
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Estado actualizado correctamente.']);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta.'], 422);
        }

        $user->update(['status' => 'inactivo']);
        $user->tokens()->delete(); // cierra sus sesiones al instante

        return response()->json(['message' => 'Usuario eliminado correctamente.']);
    }

    private function rules(?User $user = null): array
    {
        return [
            'name'              => ['required', 'string', 'max:100'],
            'email'             => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user?->id)
            ],
            'password'          => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'phone'             => ['nullable', 'string', 'max:50'],
            'role'              => ['required', Rule::in(['admin', 'user'])],
            'traveler_type'     => ['nullable', Rule::in(['mochilero', 'familia', 'pareja'])],
            'status'            => ['required', Rule::in(['activo', 'inactivo'])],
            'country_of_origin' => ['nullable', 'string', 'max:100'],
        ];
    }
}
