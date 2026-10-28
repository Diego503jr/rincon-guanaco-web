<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportReview;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function getReports()
    {
        // Trae los reportes junto con la información del usuario y la reseña original
        $reports = ReportReview::with(['reporter', 'review.user'])
            ->where('status', 'activo')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reports);
    }

    //Descartar reporte (inactivo)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'string']
        ]);

        $report = \App\Models\ReportReview::findOrFail($id);
        $report->status = $request->status;
        $report->save();

        return response()->json(['message' => 'Estado actualizado correctamente.']);
    }
}