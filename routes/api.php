<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\BookingController;
use  App\Http\Controllers\ServiceController;
use  App\Http\Controllers\ReviewController;
use App\Models\Event;

Route::get('/events', [EventController::class, 'index']);

// Route to create a new event
Route::post('/events', [EventController::class, 'store']);

// Route to show a specific event by id
Route::get('/events/{id}', [EventController::class, 'show']);

// Route to update an existing event
Route::put('/events/{id}', [EventController::class, 'update']);

// Route to delete an event
Route::delete('/events/{id}', [EventController::class, 'destroy']);

Route::post('/halls', [HallController::class, 'store']);

Route::post('/services', [ServiceController::class, 'store']);
Route::get('/halls/search', [HallController::class, 'showByName']);
Route::get('/venues/search', [VenueController::class, 'showByName']);
Route::get('/services', [ServiceController::class, 'index']);

// Route to show a service by id
Route::get('/services/{id}', [ServiceController::class, 'show']);

Route::post('/bookings/{bookingId}/review', [ReviewController::class, 'store']);
// Route to update a service by id
Route::put('/services/{id}', [ServiceController::class, 'update']);

// Route to delete a service by id
Route::delete('/services/{id}', [ServiceController::class, 'destroy']);
Route::get('/users/{userId}/bookings', [BookingController::class, 'getBookingsByStatus']);

Route::get('/venues/rated', [VenueController::class, 'getVenueRatings']);


Route::get('/halls-with-venues-events', [HallController::class, 'getHallsWithVenuesAndEvents']);
Route::get('/venues/sorted-by-price', [VenueController::class, 'getVenuesSortedByPrice']);
Route::get('/venues/sorted-by-halls', [VenueController::class, 'getVenuesSortedByHalls']);

Route::get('/events', [EventController::class, 'index']); // Show all events
Route::post('/events', [EventController::class, 'store']); // Add new event
Route::put('/events/{id}', [EventController::class, 'update']); // Edit event
Route::delete('/events/{id}', [EventController::class, 'destroy']); // Delete event

Route::get('/users/{id}', [UserController::class, 'show']);

// Route to update user information
Route::put('/users/{id}', [UserController::class, 'update']);


Route::get('/users/{id}/bookings', [UserController::class, 'showBookings']);


Route::post('/bookings', [BookingController::class, 'store']);


Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']);

Route::get('/users/{userId}/halls/{hallId}/bookings', [BookingController::class, 'showBookingsForUserInHall']);

Route::get('/bookings', [BookingController::class, 'index']);

// Route to delete a user
Route::delete('/users/{id}', [UserController::class, 'destroy']);

Route::get('/halls', [HallController::class, 'index']);
Route::post('/venues', [VenueController::class, 'store']);
// Route to return all events
Route::get('/events', [EventController::class, 'index']);

// Route to return all users
Route::get('/users', [UserController::class, 'index']);
// Public routes (no authentication required)
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/search-halls', [HallController::class, 'search']);
Route::get('/venues/hall/{hallId}', [VenueController::class, 'getVenuesByHallId']);
Route::get('/venues/{venueId}', [VenueController::class, 'getVenueById']);

//show specific hall
Route::get('/show-specific-hall/{id}',[HallController::class,'showSpecificHall']);
// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Get authenticated user details
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Admin-only route
    Route::get('/admin', function () {
        return 'Admin Page';
    })->middleware('role:Admin');

    // User-only route
    Route::get('/user-role', function () {
        return 'User Page';
    })->middleware('role:User');


});
