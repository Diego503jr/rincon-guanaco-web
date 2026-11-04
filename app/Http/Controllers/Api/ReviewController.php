<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->query('search');

        $reviews = Review::with(['user', 'destination'])
            ->when($search, function ($query, $search) {
                $query->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('destination', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            // Cambia orderByDesc('created_at') por esto:
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($reviews);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'rating'         => 'required|integer|min:1|max:5',
            'travel_date'    => 'required|date',
            'comment'        => 'nullable|string',
        ]);

        $review = Review::create([
            'user_id'        => $request->user()->id,
            'destination_id' => $validated['destination_id'],
            'rating'         => $validated['rating'],
            'travel_date'    => $validated['travel_date'],
            'comment'        => $validated['comment'],
        ]);

        return response()->json(['message' => 'Reseña creada', 'data' => $review], 201);
    }
}
