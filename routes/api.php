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
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ReviewController;


Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/events', [EventController::class, 'index']);

Route::get('/users/{userId}/bookings', [UserController::class, 'showBookings']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
Route::get('/users/{userId}/bookings', [BookingController::class, 'getBookingsByStatus']);
Route::get('/profile', [UserController::class, 'profile']);

//halls:
Route::get('/halls', [HallController::class, 'index']);
Route::get('/halls/search', [HallController::class, 'showByName']);
Route::get('/search-halls', [HallController::class, 'search']);
//Route::get('/halls/search', [HallController::class, 'search']);
//Route::get('/halls/by-name', [HallController::class, 'showByName']);
Route::get('/halls/{id}', [HallController::class, 'showSpecificHall']);
//booking:
Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']);

//venuse:
Route::get('/venues/search', [VenueController::class, 'showByName']);
Route::get('/venues/all-with-halls', [VenueController::class, 'getAllVenuesWithHalls']);
Route::get('/venues/rated', [VenueController::class, 'getVenueRatings']);
Route::get('/venues/hall/{hallId}', [VenueController::class, 'getVenuesByHallId']);
Route::get('/venues/by-name', [VenueController::class, 'showByName']);
Route::get('/venues/{venueId}', [VenueController::class, 'getVenueById']);
Route::get('/venues/sorted/price', [VenueController::class, 'getVenuesSortedByPrice']);
Route::get('/venues/sorted/halls', [VenueController::class, 'getVenuesSortedByHalls']);
Route::get('/venues/ratings', [VenueController::class, 'getVenueRatings']);
Route::get('/venues/by-hall/{hallName}', [VenueController::class, 'getVenuesByHallName']);

//user:
Route::get('/users/{id}', [UserController::class, 'show']);


//events:
        // List all events
    // Show specific event
//service:
Route::get('/services/hall/{hallName}', [ServiceController::class, 'getServicesByHallName']);
Route::get('/services-with-halls', [ServiceController::class, 'getServicesWithHalls']);
Route::get('/services', [ServiceController::class, 'index']);



    // ----------------- USER ROUTES ----------------- //
    Route::middleware('role:User')->group(function () {
        //Route::get('/users/{userId}/bookings', [UserController::class, 'showBookings']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::post('/bookings/{bookingId}/review', [ReviewController::class, 'store']);


    });


// ----------------- ADMIN & MANAGER SHARED ROUTES ----------------- //

    Route::middleware('role:Manager|Admin')->group(function () {
        Route::put('/halls/{id}', [HallController::class, 'update']);
        Route::put('/venues/{id}', [VenueController::class, 'update']);
        Route::apiResource('/services', ServiceController::class);
        Route::post('/services/hall/{hallName}', [ServiceController::class, 'storeForHall']);
        Route::get('/events/{id}', [EventController::class, 'show']);
        Route::delete('/venues/{id}', [VenueController::class, 'destroy']);
        //Route::get('/events', [EventController::class, 'index']);
        Route::get('/services/with-halls', [ServiceController::class, 'getServicesWithHalls']);
        Route::get('/services/{id}', [ServiceController::class, 'show']);
        Route::get('/users/{userId}/halls/{hallId}/bookings', [BookingController::class, 'showBookingsForUserInHall']);
        Route::get('/halls/{hallId}/venues-with-bookings', [HallController::class, 'getVenuesWithBookings']);
        //Route::get('/users/{userId}/bookings', [BookingController::class, 'getBookingsByStatus']);
//Route::get('/users/{userId}/bookings', [UserController::class, 'showBookings']);
    });


    // ----------------- ADMIN ROUTES ----------------- //
    Route::middleware('role:Admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        //Route::apiResource('/events', EventController::class);
        //Route::put('/users/{id}', [UserController::class, 'update']);
        Route::get('/halls-with-venues-events', [HallController::class, 'getHallsWithVenuesAndEvents']);
        Route::get('/bookings', [BookingController::class, 'index']);


 //halls:

Route::post('/halls', [HallController::class, 'store']);

Route::delete('/halls/{id}', [HallController::class, 'destroy']);

//booking:
Route::post('/bookings', [BookingController::class, 'store']);
Route::post('/bookings/{bookingId}/review', [ReviewController::class, 'store']);





//events:
         // List all events
Route::post('/events', [EventController::class, 'store']);         // Create new event
    // Show specific event
Route::put('/events/{id}', [EventController::class, 'update']);    // Update event
Route::delete('/events/{id}', [EventController::class, 'destroy']); // Delete event
//service:



Route::post('/services', [ServiceController::class, 'store']);
Route::put('/services/{id}', [ServiceController::class, 'update']);
Route::delete('/services/{id}', [ServiceController::class, 'destroy']);
Route::post('/halls/{hallName}/services', [ServiceController::class, 'storeForHall']);
Route::get('/venues/with-halls', [VenueController::class, 'getAllVenuesWithHalls']);

    });


});
