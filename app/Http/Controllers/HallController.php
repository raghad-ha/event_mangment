<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hall;
use App\Models\Venue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

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

    // Filter by Hall location (if provided)
    if ($request->has('location') && $request->location) {
        $query->whereHas('hall', function ($query) use ($request) {
            $query->where('location', 'like', '%' . $request->location . '%');
        });
    }

    // Filter by Venue capacity (if provided)
    if ($request->has('capacity') && $request->capacity) {
        $query->where('capacity', '=', $request->capacity);
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
    if ($this->isManagerAndUnauthorized($request->hallName)) {
        return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
    }

    // Get the hall name from the request
    $hallName = $request->input('hallName');

    // Find the hall and load venues using Eloquent relationships
    $hall = Hall::where('name', $hallName)->with('venues')->first();

    if (!$hall) {
        return response()->json(['message' => 'Hall not found.'], 404);
    }

    return response()->json([
        'hall_name' => $hall->name,
        'venues' => $hall->venues
    ]);
}



    public function index()
    {
        $halls = Hall::all(); // Get all halls from the database
        return response()->json($halls);
    }
    public function showSpecificHall(Request $request , $id){
        if ($this->isManagerAndUnauthorized($request->hallName)) {
            return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
        }
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
            'description' => 'nullable|string',
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
            'description' => $request->description,
            'image' => json_encode($imageUrls),  // Convert the array to JSON
        ]);

        // Return the created hall as a response
        return response()->json($hall, 201);
    }
    public function destroy($id)
    {


        // Find the hall by ID
        $hall = Hall::find($id);

        // If the hall does not exist, return an error
        if (!$hall) {
            return response()->json(['message' => 'Hall not found.'], 404);
        }

        // Delete the hall
        $hall->delete();

        // Return a success message
        return response()->json(['message' => 'Hall deleted successfully.'], 200);
    }
public function getVenuesWithBookings($hallId)
    {
        if ($this->isManagerAndUnauthorizedById($hallId)) {
            return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
        }
        // Fetch the hall by its ID
        $hall = Hall::find($hallId);

        // If the hall is not found, return a 404 error
        if (!$hall) {
            return response()->json(['message' => 'Hall not found.'], 404);
        }

        // Get the venues associated with the hall
        $venues = $hall->venues;

        // Initialize an empty array to store the result
        $result = [
            'hall' => $hall,
            'venues' => []
        ];

        // Loop through each venue to check for bookings, users, and services
        foreach ($venues as $venue) {
            // Get the bookings for the venue, including the associated user and services
            $bookings = $venue->bookings()
                ->with(['user', 'services'])  // Eager load the user and services for each booking
                ->get();

            // If there are bookings, add the venue and its bookings with users and services to the result
            if ($bookings->isNotEmpty()) {
                $result['venues'][] = [
                    'venue' => $venue,
                    'bookings' => $bookings->map(function ($booking) {
                        return [
                            'booking_id' => $booking->id,
                            'booking_date' => $booking->booking_date,
                            'status' => $booking->status,
                            'user' => [
                                'id' => $booking->user->id,
                                'name' => $booking->user->name,
                                'email' => $booking->user->email
                            ],
                            'services' => $booking->services->map(function ($service) {
                                return [
                                    'id' => $service->id,
                                    'name' => $service->name,
                                    'price' => $service->price
                                ];
                            })
                        ];
                    }),
                ];
            }
        }

        // Return the result with venues that have bookings
        return response()->json($result);
    }

public function update(Request $request, $id)
{
    // Find the hall to update
    $hall = Hall::findOrFail($id);

    // Validate the incoming request
    $request->validate([
        'name' => 'sometimes|string|max:255',
        'location' => 'sometimes|string|max:255',
        'description' => 'nullable|string',
        'images' => 'sometimes|array',  // Optional array of images
        'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg', // Validate each image if provided
        'remove_images' => 'sometimes|array', // Array of image URLs to remove
    ]);

    // Update basic fields if they exist in the request
    $updateData = [];
    if ($request->has('name')) {
        $updateData['name'] = $request->name;
    }
    if ($request->has('location')) {
        $updateData['location'] = $request->location;
    }
    if ($request->has('description')) {
        $updateData['description'] = $request->description;
    }

    // Handle image updates if new images are provided
    if ($request->hasFile('images')) {
        $imageUrls = json_decode($hall->image, true) ?? [];

        // Add new images
        foreach ($request->file('images') as $image) {
            $timestamp = now()->format('Ymd_His');
            $imageExtension = $image->getClientOriginalExtension();
            $imageName = "venue_{$timestamp}.{$imageExtension}";

            $imagePath = $image->storeAs('venues', $imageName, 'public');
            $imageUrl = Storage::url($imagePath);

            $imageUrls[] = $imageUrl;
        }

        $updateData['image'] = json_encode($imageUrls);
    }

    // Handle image removal if requested
    if ($request->has('remove_images')) {
        $currentImages = json_decode($hall->image, true) ?? [];
        $imagesToRemove = $request->remove_images;

        // Filter out images to remove
        $updatedImages = array_filter($currentImages, function($imageUrl) use ($imagesToRemove) {
            return !in_array($imageUrl, $imagesToRemove);
        });

        // Also delete the actual files from storage
        foreach ($imagesToRemove as $imageUrl) {
            $path = str_replace('/storage/', '', $imageUrl);
            Storage::disk('public')->delete($path);
        }

        $updateData['image'] = json_encode(array_values($updatedImages));
    }

    // Update the hall record
    $hall->update($updateData);

    return response()->json([
        'message' => 'Hall updated successfully',
        'hall' => $hall
    ]);
}
}
