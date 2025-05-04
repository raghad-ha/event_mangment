<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    public function show($id)
    {
        // Find the service by id
        $service = Service::find($id);

        // Check if the service exists
        if (!$service) {
            return response()->json(['message' => 'Service not found.'], 404);
        }

        // Return the service information
        return response()->json($service);
    }

    // Method to update service by id
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric',
        ]);

        // Find the service by id
        $service = Service::find($id);


        // Check if the service exists
        if (!$service) {
            return response()->json(['message' => 'Service not found.'], 404);
        }

        // Update the service details
        $service->name = $request->name;
        $service->price = $request->price;

        // Save the changes
        $service->save();


        // Return the updated service
        return response()->json($service);
    }

    // Method to delete service by id
    public function destroy($id)
    {
        // Find the service by id
        $service = Service::find($id);

        // Check if the service exists
        if (!$service) {
            return response()->json(['message' => 'Service not found.'], 404);
        }

        // Delete the service
        $service->delete();

        // Return a success message
        return response()->json(['message' => 'Service deleted successfully.']);
    }
    public function index(Request $request)
    {
        // Optional: Pagination for large datasets
        $perPage = $request->query('per_page', 10);  // Default to 10 services per page
        $services = Service::paginate($perPage); // Paginate the results

        // Return the services as a JSON response
        return response()->json($services);
    }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name', // Name must be unique and not null
            'price' => 'required|numeric|min:0', // Price must be a positive number
        ]);

        // Create a new service using the validated data
        $service = Service::create([
            'name' => $request->name, // Service name
            'price' => $request->price, // Service price
        ]);

        // Return the created service as a response
        return response()->json($service, 201);
    }
}
