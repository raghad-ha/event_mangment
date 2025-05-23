<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Booking;
use App\Models\User;
class ReviewController extends Controller
{
    public function store(Request $request, $bookingId)
    {

        $userId=Auth::id();
        // Validate the incoming request
        $request->validate([
            'rating' => 'required|integer|between:1,5',  // Rating must be between 1 and 5
            'comment' => 'nullable|string|max:1000',  // Comment can be null but must be a string if present
        ]);
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        // Ensure the booking is associated with the user
        if ($booking->user_id !==  $userId) {
            return response()->json(['message' => 'This booking does not belong to the user.'], 403);
        }

        // Create the review
        $review = Review::create([
            'user_id' =>  $userId,            // User ID provided in the request
            'booking_id' => $booking->id,      // The booking being reviewed
            'rating' => $request->rating,      // Rating (1-5)
            'comment' => $request->comment,    // Comment (nullable)
        ]);

        // Return the created review as a response
        return response()->json($review, 201);
    }
}
