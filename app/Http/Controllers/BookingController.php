<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;use App\Models\Booking;
use App\Models\Venue;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id', // Ensure the user exists
            'venue_id' => 'required|exists:venues,id', // Ensure the venue exists
            'event_type_id' => 'required|exists:event_types,id', // Ensure the event type exists
            'booking_date' => 'required|date', // Ensure the date is valid
            'status' => 'required|in:Pending,Confirmed,Cancelled', // Status validation
        ]);

        // If validation fails, return error messages
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Check if the venue is available on the selected date (optional)
        $existingBooking = Booking::where('venue_id', $request->venue_id)
->where('booking_date', $request->booking_date)->first();

        if ($existingBooking) {
            return response()->json(['message' => 'The venue is already booked for this date.'], 400);
        }

        // Create a new booking
        $booking = Booking::create([
            'user_id' => $request->user_id,
            'venue_id' => $request->venue_id,
            'event_type_id' => $request->event_type_id,
            'booking_date' => $request->booking_date,
            'status' => $request->status,
        ]);

        // Return the created booking as a response
        return response()->json($booking, 201);
    }
    public function updateStatus(Request $request, $id)
    {
        // Validate the incoming request to ensure the status is valid
        $request->validate([
            'status' => 'required|in:Pending,Confirmed,Cancelled', // Status validation
        ]);

        // Find the booking by id
        $booking = Booking::find($id);

        // Check if the booking exists
        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        // Update the booking status
        $booking->status = $request->status;

        // Save the changes
        $booking->save();

        // Return the updated booking as a response
        return response()->json($booking);
    }
    public function showBookingsForUserInHall(Request $request, $userId, $hallId)
    {
        // Find the user by ID
        $user = User::find($userId);

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Find all venues in the specific hall
        $venues = Venue::where('hall_id', $hallId)->get();

        // Check if the hall has venues
        if ($venues->isEmpty()) {
            return response()->json(['message' => 'No venues found for this hall.'], 404);
        }

        // Get the bookings for the user in the venues of the specific hall
        $bookingIds = $venues->pluck('id');
        $bookings = Booking::where('user_id', $userId)
                            ->whereIn('venue_id', $bookingIds)
                            ->get();

        // If no bookings found, return a message
        if ($bookings->isEmpty()) {
            return response()->json(['message' => 'No bookings found for this user in this hall.'], 404);
        }

        // Return the bookings as a response
        return response()->json($bookings);
    }
    public function index(Request $request)
    {
        // Optional: Pagination for large datasets
        $perPage = $request->query('per_page', 10);  // Default to 10 bookings per page
        $bookings = Booking::paginate($perPage); // Paginate the results

        // Return the bookings as a JSON response
        return response()->json($bookings);
    }
    public function getBookingsByStatus(Request $request, $userId)
    {
        // Validate the status parameter if provided
        $request->validate([
            'status' => 'nullable|in:Pending,Confirmed,Completed',  // Optional status filter by name
            'status_code' => 'nullable|in:1,2,3' // Optional status filter by numeric code
        ]);

        // Find the user by id
        $user = User::find($userId);

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Query the bookings based on user_id
        $query = Booking::where('user_id', $userId);

        // If a status name is provided, filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // If a status code is provided, map it to its corresponding status name
        if ($request->has('status_code')) {
            $statusMap = [
                1 => 'Completed',
                2 => 'Confirmed',
                3 => 'Pending'
            ];
            $statusName = $statusMap[$request->status_code];
            $query->where('status', $statusName);
        }

        // Get the filtered bookings
        $bookings = $query->get();

        // If no bookings are found
        if ($bookings->isEmpty()) {
            return response()->json(['message' => 'No bookings found for this user.'], 404);
        }

        // Return the bookings as a response
        return response()->json($bookings);
    }
}
