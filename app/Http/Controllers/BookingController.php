<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;use App\Models\Booking;
use App\Models\Venue;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaiseController;
use Illuminate\Support\Facades\Auth;

class BookingController extends BaiseController
{
public function store(Request $request)
    {
        $userId = Auth::id();

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'venue_id' => 'required|exists:venues,id', // Ensure the venue exists
            'event_type_id' => 'required|exists:event_types,id', // Ensure the event type exists
            'booking_date' => 'required|date', // Ensure the date is valid
            'status' => 'in:Pending,Confirmed,Cancelled', // Optional, with valid values
            'services' => 'required|array',  // Validate that services are provided
            'services.*' => 'exists:services,id'  // Ensure each selected service is valid
        ]);

        // If validation fails, return error messages
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Fetch the venue and its associated hall
        $venue = Venue::find($request->venue_id);
        $hall = $venue->hall;  // Assuming Venue has a 'hall' relationship

        // Ensure the services belong to the hall of the selected venue
        $availableServices = $hall->services->pluck('id')->toArray();

        // Check if each selected service is valid for the hall
        $invalidServices = array_diff($request->services, $availableServices);

        if (!empty($invalidServices)) {
            return response()->json(['message' => 'One or more selected services are not available for this hall.'], 400);
        }

        // Check if the venue is available on the selected date (optional)
        $existingBooking = Booking::where('venue_id', $request->venue_id)
            ->where('booking_date', $request->booking_date)
            ->first();

        if ($existingBooking) {
            return response()->json(['message' => 'The venue is already booked for this date.'], 400);
        }

        // Create a new booking with default status 'Pending' if not provided
        $booking = Booking::create([
            'user_id' => $userId,
            'venue_id' => $request->venue_id,
            'event_type_id' => $request->event_type_id,
            'booking_date' => $request->booking_date,
            'status' => $request->status ?? 'Pending', // default to 'Pending'
        ]);

        // Attach the selected services to the booking
        $booking->services()->attach($request->services);

        return response()->json($booking, 201);
    }
    public function updateStatus(Request $request, $id)
    {
        $userId = Auth::id();
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

        if ($this->isManagerAndUnauthorizedById($hallId)) {
            return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
        }
        // Find the user by ID
        $userId = Auth::id();




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
        $user = Auth::id();

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
