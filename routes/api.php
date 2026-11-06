<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ModerationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ItineraryController;
use App\Http\Controllers\Api\ItineraryActivityController;
use App\Http\Controllers\Api\ItineraryAccommodationController;
use App\Http\Controllers\Api\AccommodationController;

// Públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,11'); // app móvil
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:6,1'); // panel web

// Protegidas: requieren token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Destinos
    Route::get('/admin/destinations', [DestinationController::class, 'index']);
    Route::get('/admin/destinations/{id}', [DestinationController::class, 'show']); 
    Route::post('/admin/destinations', [DestinationController::class, 'store']);
    Route::post('/admin/destinations/{id}', [DestinationController::class, 'update']); // Usando POST con _method si se envían archivos multipart/form-data
    Route::delete('/admin/destinations/{id}', [DestinationController::class, 'destroy']); //Eliminado lógico

    // Alojamientos 
    Route::get('/admin/accommodations', [AccommodationController::class, 'index']);
    Route::get('/admin/accommodations/{id}', [AccommodationController::class, 'show']);
    Route::post('/admin/accommodations', [AccommodationController::class, 'store']);
    Route::post('/admin/accommodations/{id}', [AccommodationController::class, 'update']);
    Route::delete('/admin/accommodations/{id}', [AccommodationController::class, 'destroy']); // Eliminado lógico
    Route::get('/destinations/{destinationId}/accommodations', [AccommodationController::class, 'getByDestination']);

    //Reviews WEB
    Route::get('/admin/reports', [ModerationController::class, 'getReports']); //Listar reportes activos
    Route::patch('/admin/reports/{id}/status', [ModerationController::class, 'updateStatus']); //Eliminado lógico
    Route::delete('/admin/reports/{id}', [ModerationController::class, 'destroyReport']); //Eliminado físico

    //Reviews MOVIL
    Route::get('/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'index']); //Mostrar reseñas
    Route::post('/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']); //Crear reseña
    Route::post('/reviews/report', [\App\Http\Controllers\Api\ModerationController::class, 'store']); //Crear reporte

    //Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'stats']); //Contadores y datos de gráficos
    
    //Reportes PDF
    Route::get('/admin/pdf-reports', [ReportController::class, 'pdf']); //Generar reporte PDF (?type=&from=&to=)
    
    //Actividades
    Route::get('/admin/activities', [ActivityController::class, 'index']); 
    Route::post('/admin/activities', [ActivityController::class, 'store']); 
    Route::put('/admin/activities/{id}', [ActivityController::class, 'update']); 
    Route::delete('/admin/activities/{id}', [ActivityController::class, 'destroy']); //Eliminado lógico
    Route::get('/user/visited-destinations', [DestinationController::class, 'visitedByUser']); //Destinos por itinerario de usuario
    Route::get('/destinations/{destinationId}/activities', [ActivityController::class, 'getByDestination']);

    
    //Itinerarios (app móvil: cada usuario solo ve los suyos)
    Route::get('/itineraries', [ItineraryController::class, 'index']); 
    Route::post('/itineraries', [ItineraryController::class, 'store']); 
    Route::get('/itineraries/{id}', [ItineraryController::class, 'show']); 
    Route::patch('/itineraries/{id}', [ItineraryController::class, 'update']);
    Route::delete('/itineraries/{id}', [ItineraryController::class, 'destroy']); 

    
    //Actividades dentro del itinerario (modales Activity-Add / Activity-List)
    Route::get('/activities', [ItineraryActivityController::class, 'available']); //Actividades activas (?destination_id=)
    Route::post('/itineraries/{id}/activities', [ItineraryActivityController::class, 'store']); //Agregar actividad
    Route::patch('/itineraries/{id}/activities/{itemId}', [ItineraryActivityController::class, 'update']); //Cambiar título u hora
    Route::delete('/itineraries/{id}/activities/{itemId}', [ItineraryActivityController::class, 'destroy']); //Quitar actividad

    //Alojamientos dentro del itinerario (modal Reservar Alojamiento)
    Route::post('/itineraries/{id}/accommodations', [ItineraryAccommodationController::class, 'store']); //Reservar alojamiento
    Route::patch('/itineraries/{id}/accommodations/{itemId}', [ItineraryAccommodationController::class, 'update']); //Cambiar fechas o huéspedes
    Route::delete('/itineraries/{id}/accommodations/{itemId}', [ItineraryAccommodationController::class, 'destroy']); //Quitar alojamiento

    // Usuarios
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::post('/admin/users', [UserController::class, 'store']);
    Route::get('/admin/users/{user}', [UserController::class, 'show']);
    Route::put('/admin/users/{user}', [UserController::class, 'update']);
    Route::patch('/admin/users/{user}/status', [UserController::class, 'updateStatus']);
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy']);
});