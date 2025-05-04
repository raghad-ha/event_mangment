<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hall;
use App\Models\Venue;
use Illuminate\Support\Facades\Storage;

class HallController extends Controller
{
    public function search(Request $request)
    {
        // Start with the Venue model
        $query = Venue::query();

        // Filter by Hall name (if provided)
        if ($request->has('hall_name') && $request->hall_name) {
            $query->whereHas('hall', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->hall_name . '%');
            });
        }

        // Filter by Venue capacity (if provided)
        if ($request->has('capacity') && $request->capacity) {
            $query->where('capacity', '>=', $request->capacity);
        }

        // Filter by Venue price range (min and max)
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by Event name (if provided)
        if ($request->has('event_name') && $request->event_name) {
            $query->whereHas('events', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->event_name . '%');
            });
        }

        // Filter by Date (availability check)
        if ($request->has('date') && $request->date) {
            $query->whereDoesntHave('bookings', function ($query) use ($request) {
                $query->where('booking_date', $request->date);
            });
        }

        // Execute the query and get the results
        $venues = $query->get();

        return response()->json($venues);
    }

    public function showByName(Request $request)
    {
        // Get the hall name from the request
        $hallName = $request->input('name');

        // Search for the hall by name (case insensitive)
        $hall = Hall::where('name', 'like', '%' . $hallName . '%')->first();

        // Check if the hall exists
        if (!$hall) {
            return response()->json(['message' => 'Hall not found.'], 404);
        }

        // Return the hall information
        return response()->json($hall);
    }
    public function index()
    {
        $halls = Hall::all(); // Get all halls from the database
        return response()->json($halls);
    }
    public function showSpecificHall(Request $request , $id){
        $hall = Hall::findOrFail($id);
        if($hall){
            return response()->json([
                'msg' => "data retrieved successfully",
                'success' => true,
                'data' => $hall
            ]);

        }
        return response()->json([
            'msg' => "hall not found",
            'success' => false,
            'data' => []
        ]);
    }
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'images' => 'required|array',  // Ensure 'images' is an array
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg', // Validate each image
        ]);

        $imageUrls = [];

        // Loop through each image and store it
        foreach ($request->file('images') as $image) {
            // Generate a unique filename based on the current timestamp
            $timestamp = now()->format('Ymd_His');
            $imageExtension = $image->getClientOriginalExtension();
            $imageName = "venue_{$timestamp}.{$imageExtension}"; // Example: venue_20250407_123456.jpg

            // Store the image in the 'venues' folder (public disk) and get the path
            $imagePath = $image->storeAs('venues', $imageName, 'public');

            // Create a URL to the image (this is the path in storage)
            $imageUrl = Storage::url($imagePath);

            // Add the image URL to the array
            $imageUrls[] = $imageUrl;
        }

        // Store the hall along with the images (as JSON)
        $hall = Hall::create([
            'name' => $request->name,
            'location' => $request->location,
            'image' => json_encode($imageUrls),  // Convert the array to JSON
        ]);

        // Return the created hall as a response
        return response()->json($hall, 201);
    }

}
