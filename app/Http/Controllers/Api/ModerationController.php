<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\ReportReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModerationController extends Controller
{
    // WEB: Listar los reportes pendientes de revisión
    public function getReports()
    {
        // Trae los reportes junto con la información del usuario que reportó y la reseña con su autor
        $reports = ReportReview::with(['reporter', 'review.user'])
            ->where('status', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reports);
    }

    //Actualización de estados de reseña reportada
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:descartado,confirmado']
        ]);

        $report = ReportReview::findOrFail($id);
        $newStatus = $request->status;
        
        $reviewId = $report->review_id;
        $nota = $report->nota; 

        // DESCARTAR (Solo descarta los reportes con ese motivo (nota) específico)
        if ($newStatus === 'descartado') {
            ReportReview::where('review_id', $reviewId)
                        ->where('nota', $nota) 
                        ->whereIn('status', ['activo', 'pendiente'])
                        ->update(['status' => 'descartado']);
            
            return response()->json([
                'message' => 'Reportes descartados correctamente. La reseña sigue visible.'
            ]);
        }

        // CONFIRMAR (Oculta la reseña y cierra TODOS los reportes asociados)
        if ($newStatus === 'confirmado') {
            DB::transaction(function () use ($report, $reviewId) {
                // Ocultar la reseña
                $report->review()->update(['status' => 'inactivo']);

                // Cerrar TODOS los reportes de esta reseña, sin importar el motivo
                ReportReview::where('review_id', $reviewId)
                            ->whereIn('status', ['activo', 'pendiente'])
                            ->update(['status' => 'confirmado']); 
            });

            return response()->json([
                'message' => 'Reseña ocultada y todos los reportes asociados fueron cerrados.'
            ]);
        }
    }

    // MÓVIL: Crear un nuevo reporte de reseña
    public function store(Request $request)
    {
        $validated = $request->validate([
            'review_id'   => 'required|exists:reviews,id',
            'nota'        => 'required|string',
            'type_report' => 'required|string',
        ]);

        $report = ReportReview::create([
            'user_id'     => $request->user()->id,
            'review_id'   => $validated['review_id'],
            'nota'        => $validated['nota'],
            'type_report' => $validated['type_report'],
            'status'      => 'pendiente',
        ]);

        return response()->json([
            'message' => 'Gracias, hemos recibido tu reporte y será revisado'
        ], 201);
    }
}
