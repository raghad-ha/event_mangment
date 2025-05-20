<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\BaiseController;
use App\Models\Hall;
use Illuminate\Support\Facades\Auth;

class VenueController extends BaiseController
{
    public function getVenuesByHallId($hallId)
    {
// if ($this->isManagerAndUnauthorizedById($hallId)) {
//             return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
//         }
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

    // Find the venue by name (case-insensitive) and include the hall relationship
    $venue = Venue::with('hall') // Assuming you have a 'hall' relationship in your Venue model
        ->where('name', 'like', '%' . $venueName . '%')
        ->first();

    // Check if the venue exists
    if (!$venue) {
        return response()->json(['message' => 'Venue not found.'], 404);
    }
if ($this->isManagerAndUnauthorizedById($venue->hall_id)) {
            return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
        }
    // Return the venue information including hall_id
    return response()->json([
        'venue' => $venue,
        'hall_id' => $venue->hall_id,
        'hall_details' => $venue->hall // This will include all hall details if relationship exists
    ]);
}
    public function getVenueById($venueId)
{
    // Find the venue by its id with hall relationship eager loaded
    $venue = Venue::with('hall')->find($venueId);

    // Check if the venue exists
    if (!$venue) {
        return response()->json([
            'message' => 'Venue not found.'
        ], 404);
    }
    // Return the venue details with hall information
    return response()->json([
        'venue' => $venue,
        'hall_id' => $venue->hall_id,
        'hall_details' => $venue->hall // All hall details
    ]);
}
    public function store(Request $request)
    {

        //Ensure the manager is authorized to manage the specified hall
        if ($this->isManagerAndUnauthorized($request->hall_name)) {
           return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
       }

        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer',
            'price' => 'required|numeric',
            'hall_name' => 'required|string|exists:halls,name',  // Validate hall name
            'images' => 'required|array',  // Ensure 'images' is an array
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg',  // Validate each image
        ]);

        // Check if the hall exists based on the hall name
        $hall = Hall::where('name', $request->hall_name)->first();

        // Ensure the hall exists
        if (!$hall) {
            return response()->json(['message' => 'Hall not found.'], 404);
        }

        // Ensure the manager is authorized to access the hall (check if hall belongs to the manager)


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
            'hall_id' => $hall->id,  // Use hall ID to associate the venue with the hall
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
    public function destroy($id)
    {
        // Ensure the manager is authorized to delete this venue
        if ($this->isManagerAndUnauthorizedById($id)) {
            return response()->json(['message' => 'You are not authorized to delete a venue for this hall.'], 403);
        }

        // Find the venue by ID
        $venue = Venue::find($id);

        // If the venue does not exist, return an error
        if (!$venue) {
            return response()->json(['message' => 'Venue not found.'], 404);
        }

        // Delete the venue
        $venue->delete();

        // Return a success message
        return response()->json(['message' => 'Venue deleted successfully.'], 200);
    }

public function getAllVenuesWithHalls()
{
    // جلب جميع القاعات مع معلومات الصالة المرتبطة بها
    $venues = Venue::with('hall')->get();

    // إذا لم توجد قاعات
    if ($venues->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No venues found in any hall.'
        ], 404);
    }

    // إرجاع القاعات مع الصالات
    return response()->json([
        'success' => true,
        'data' => $venues
    ]);
}
public function update(Request $request, $id)
{
    if ($this->isManagerAndUnauthorized($request->hall_name)) {
            return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
        }
    // Validate the incoming request
    $request->validate([
        'name' => 'sometimes|string|max:255',
        'capacity' => 'sometimes|integer',
        'price' => 'sometimes|numeric',
        'hall_name' => 'sometimes|string|exists:halls,name',
        'images' => 'sometimes|array',
        'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
    ]);

    // Find the venue by ID
    $venue = Venue::find($id);

    if (!$venue) {
        return response()->json(['message' => 'Venue not found.'], 404);
    }

    // Update hall_id if hall_name is provided
    if ($request->has('hall_name')) {
        $hall = Hall::where('name', $request->hall_name)->first();
        if (!$hall) {
            return response()->json(['message' => 'Hall not found.'], 404);
        }
        $venue->hall_id = $hall->id;
    }

    // Update basic fields
    $venue->fill($request->only(['name', 'capacity', 'price']));

    // Handle image updates
    if ($request->hasFile('images')) {
        // Delete old images (optional)
        $oldImages = json_decode($venue->image, true);
        if (is_array($oldImages)) {
            foreach ($oldImages as $oldImage) {
                $oldImagePath = str_replace('/storage/', '', $oldImage);
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        // Upload new images
        $imageUrls = [];
        foreach ($request->file('images') as $image) {
            $timestamp = now()->format('Ymd_His');
            $imageExtension = $image->getClientOriginalExtension();
            $imageName = "venue_{$timestamp}.{$imageExtension}";
            $imagePath = $image->storeAs('venues', $imageName, 'public');
            $imageUrl = Storage::url($imagePath);
            $imageUrls[] = $imageUrl;
        }

        $venue->image = json_encode($imageUrls);
    }

    $venue->save();

    return response()->json([
        'message' => 'Venue updated successfully',
        'data' => $venue
    ], 200);
}

public function getVenuesByHallName($hallName)
{
    // البحث عن جميع الصالات التي تحتوي على الاسم المدخل
    $halls = Hall::where('name', 'like', '%' . $hallName . '%')->get();

    if ($halls->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No halls found',
            'searched_name' => $hallName
        ], 404);
    }

    // تجهيز البيانات مع القاعات وقاعاتها المرتبطة
    $response = [
        'success' => true,
        'total_halls' => $halls->count(),
        'halls' => $halls->map(function ($hall) {
            return [
                'id' => $hall->id,
                'name' => $hall->name,
                'location' => $hall->location,
                'total_venues' => $hall->venues()->count(),
                'venues' => $hall->venues->map(function ($venue) {
                    return [
                        'id' => $venue->id,
                        'name' => $venue->name,
                        'capacity' => $venue->capacity,
                        'price' => $venue->price,
                        'image' => $venue->image,
                        'created_at' => optional($venue->created_at)->format('Y-m-d H:i:s'),
                        'updated_at' => optional($venue->updated_at)->format('Y-m-d H:i:s')
                    ];
                })
            ];
        })
    ];

    return response()->json($response);
}

}
