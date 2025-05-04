<?php

namespace App\Http\Controllers;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        // Retrieve all events from the database
        $events = Event::all();

        // Return the events as a JSON response
        return response()->json($events);
    }

    // Method to store a new event
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',   // Event name is required and must be a string
            'description' => 'nullable|string',    // Event description is optional
            'event_type_id' => 'required|exists:event_types,id',  // Ensure the event type exists
        ]);

        // Create a new event using the validated data
        $event = Event::create([
            'name' => $request->name,               // Event name
            'description' => $request->description,  // Event description
            'event_type_id' => $request->event_type_id, // Event type ID
        ]);

        // Return the created event as a response
        return response()->json($event, 201);
    }

    // Method to show a specific event by id
    public function show($id)
    {
        // Find the event by its ID
        $event = Event::find($id);

        // If the event doesn't exist, return a 404 error
        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        // Return the event details as a response
        return response()->json($event);
    }

    // Method to update an existing event
    public function update(Request $request, $id)
    {
        // Find the event by its ID
        $event = Event::find($id);

        // If the event doesn't exist, return a 404 error
        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',    // Event name is required
            'description' => 'nullable|string',     // Event description is optional
            'event_type_id' => 'required|exists:event_types,id',  // Ensure the event type exists
        ]);

        // Update the event with the validated data
        $event->update([
            'name' => $request->name,               // Update event name
            'description' => $request->description,  // Update event description
            'event_type_id' => $request->event_type_id, // Update event type ID
        ]);

        // Return the updated event as a response
        return response()->json($event);
    }

    // Method to delete an event
    public function destroy($id)
    {
        // Find the event by its ID
        $event = Event::find($id);

        // If the event doesn't exist, return a 404 error
        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        // Delete the event
        $event->delete();

        // Return a success message
        return response()->json(['message' => 'Event deleted successfully.']);
    }
}
