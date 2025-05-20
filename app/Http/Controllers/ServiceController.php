<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Hall;
use App\Http\Controllers\BaiseController;

class ServiceController extends BaiseController
{
    public function show($id)
    {
        if ($this->isManagerAndUnauthorizedById($id)) {
            return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
        }
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
        if ($this->isManagerAndUnauthorizedById($id)) {
            return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
        }
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
        if ($this->isManagerAndUnauthorizedById($id)) {
            return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
        }
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
       if ($this->isManagerAndUnauthorized($request->hallName)) {
         return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
        }
        // Optional: Pagination for large datasets
        $perPage = $request->query('per_page', default: 10);  // Default to 10 services per page
        $services = Service::paginate($perPage); // Paginate the results

        // Return the services as a JSON response
        return response()->json($services);
    }

    public function store(Request $request)
    {
        if ($this->isManagerAndUnauthorized($request->hallName)) {
            return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
        }
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
    public function storeForHall(Request $request, $hallName)
    {
       if ($this->isManagerAndUnauthorized($hallName)) {
           return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
       }
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name', // Service name must be unique
            'price' => 'required|numeric|min:0', // Price must be a positive number
        ]);

        // Find the hall by its name
        $hall = Hall::where('name', $hallName)->first();

        // Check if the hall exists
        if (!$hall) {
            return response()->json(['message' => 'Hall not found.'], 404);
        }

        // Create the new service
        $service = Service::create([
            'name' => $request->name,  // Service name
            'price' => $request->price,  // Service price
        ]);

        // Associate the service with the hall using the pivot table (many-to-many)
        $hall->services()->attach($service->id);

        // Return the created service and hall as a response
        return response()->json([
            'message' => 'Service created and associated with the hall.',
            'service' => $service,
            'hall' => $hall,
        ], 201);
    }
    public function getServicesByHallName($hallName)
     {
    //             if ($this->isManagerAndUnauthorized($hallName)) {
    //        return response()->json(['message' => 'You are not authorized to manage services for this hall.'], 403);
    //     }


        // Find the hall by its name
        $hall = Hall::where('name', $hallName)->first();

        // Check if the hall exists
        if (!$hall) {
            return response()->json(['message' => 'Hall not found.'], 404);
        }

        // Get the services associated with the hall
        $services = $hall->services;  // This uses the many-to-many relationship

        // Return the services as a JSON response
        return response()->json($services);
    }
    public function getServicesWithHalls()
    {
        // Fetch all services with the associated halls (using eager loading)
        $services = Service::with('halls')->get();

        // Return the services along with the halls they are associated with
        return response()->json($services);
    }
}
