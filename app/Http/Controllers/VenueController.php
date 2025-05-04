<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VenueController extends Controller
{
    public function getVenuesByHallId($hallId)
    {
        // Get the venues that belong to the given hall_id
        $venues = Venue::where('hall_id', $hallId)->get();

        // Check if no venues were found
        if ($venues->isEmpty()) {
            // Return a custom message if no venues are found
            return response()->json([
                'message' => 'There are no venues for this hall.'
            ], 404); // You can use a 404 or any other HTTP status code
        }

        // Return the venues as a JSON response if they exist
        return response()->json($venues);
    }

    public function showByName(Request $request)
    {
        // Get the venue name from the request
        $venueName = $request->input('name');

        // Search for the venue by name (case insensitive)
        $venue = Venue::where('name', 'like', '%' . $venueName . '%')->first();

        // Check if the venue exists
        if (!$venue) {
            return response()->json(['message' => 'Venue not found.'], 404);
        }

        // Return the venue information
        return response()->json($venue);
    }
    public function getVenueById($venueId)
    {
        // Find the venue by its id
        $venue = Venue::find($venueId);

        // Check if the venue exists
        if (!$venue) {
            // Return a custom message if the venue is not found
            return response()->json([
                'message' => 'Venue not found.'
            ], 404); // You can use 404 or any other HTTP status code
        }

        // Return the venue details as a JSON response
        return response()->json($venue);
    }
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer',
            'price' => 'required|numeric',
            'hall_id' => 'required|exists:halls,id',  // Ensure the hall exists
            'images' => 'required|array',  // Ensure 'images' is an array
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg',  // Validate each image
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

        // Store the venue along with the images (as JSON)
        $venue = Venue::create([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'price' => $request->price,
            'hall_id' => $request->hall_id,  // Save the hall ID
            'image' => json_encode($imageUrls),  // Convert the array to JSON
        ]);

        // Return the created venue as a response
        return response()->json($venue, 201);
    }


public function getVenuesSortedByPrice()
{
    $venues = Venue::orderBy('price', 'desc')->get();

    return response()->json([
        'success' => true,
        'data' => $venues
    ]);
}


public function getVenuesSortedByHalls()
{
    $venues = Venue::withCount('halls')->orderBy('halls_count', 'desc')->get();

    return response()->json([
        'success' => true,
        'data' => $venues
    ]);
}
public function getVenueRatings()
    {
        // Get all venues
        $venues = Venue::all();

        // Map through each venue and calculate the average rating
        $venuesWithRatings = $venues->map(function ($venue) {
            // Get all reviews related to bookings that are tied to this venue
            $reviews = $venue->bookings()->with('reviews')->get()->pluck('reviews')->flatten();

            // Calculate the average rating for each venue
            if ($reviews->isNotEmpty()) {
                $venue->average_rating = $reviews->avg('rating');
            } else {
                $venue->average_rating = 0; // If no reviews, set rating as 0
            }

            return $venue;
        });

        // Sort the venues by average rating in descending order
        $sortedVenues = $venuesWithRatings->sortByDesc('average_rating');

        // Return the venues along with their average ratings
        return response()->json($sortedVenues);
    }

}
