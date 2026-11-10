<?php

namespace App\Http\Controllers\Api; 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function sendSupportMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        Mail::raw("Mensaje de soporte de: {$user->name} ({$user->email})\n\n{$validated['message']}", function ($mail) {
            $mail->to('sarai.villalta12@gmail.com')
                 ->subject('Nuevo mensaje de soporte - App Rincón Guanaco');
        });

        return response()->json([
            'message' => 'Tu comentario ha sido enviado con éxito a los administradores.',
        ], 200);
    }
}