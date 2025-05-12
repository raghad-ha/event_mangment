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

// Public Routes

Route::get('/halls', [HallController::class, 'index']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/halls/search', [HallController::class, 'showByName']);
Route::get('/search-halls', [HallController::class, 'search']);
Route::get('/venues/search', [VenueController::class, 'showByName']);
Route::post('/halls', [HallController::class, 'store']);
Route::post('/venues', [VenueController::class, 'store']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/venues/all-with-halls', [VenueController::class, 'getAllVenuesWithHalls']);
Route::get('/services/hall/{hallName}', [ServiceController::class, 'getServicesByHallName']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::get('/users/{userId}/bookings', [UserController::class, 'showBookings']);
//Route::post('/bookings', [BookingController::class, 'store']);
Route::post('/bookings/{bookingId}/review', [ReviewController::class, 'store']);
Route::get('/halls/{hallId}/venues-with-bookings', [HallController::class, 'getVenuesWithBookings']);
Route::get('/services-with-halls', [ServiceController::class, 'getServicesWithHalls']);
// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
//هي للكل 
    // ----------------- USER ROUTES ----------------- //
    Route::middleware('role:User')->group(function () {
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::get('/users/{userId}/bookings', [UserController::class, 'showBookings']);
        //Route::post('/bookings', [BookingController::class, 'store']);
        Route::post('/bookings/{bookingId}/review', [ReviewController::class, 'store']);
        Route::get('/users', [UserController::class, 'index']);
    });

    // ----------------- SEARCH ROUTES (Shared) ----------------- //
    // ----------------- MANAGER ROUTES ----------------- //
    Route::middleware(['auth:sanctum', 'role:Admin,Manager'])->group(function () {
        Route::get('/users/{userId}/halls/{hallId}/bookings', [BookingController::class, 'showBookingsForUserInHall']);
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/venues', [VenueController::class, 'store']);

    });

    // ----------------- ADMIN ROUTES ----------------- //
    Route::middleware('role:Admin')->group(function () {
        //Route::get('/users', [UserController::class, 'index']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        Route::apiResource('/events', EventController::class);
       // Route::post('/bookings', [BookingController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::get('/services/hall/{hallName}', [ServiceController::class, 'getServicesByHallName']);
        Route::apiResource('/services', ServiceController::class);
        Route::get('/halls-with-venues-events', [HallController::class, 'getHallsWithVenuesAndEvents']);
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
        Route::get('/users/{userId}/halls/{hallId}/bookings', [BookingController::class, 'showBookingsForUserInHall']);
    });

    // ----------------- ADMIN & MANAGER SHARED ROUTES ----------------- //

    Route::middleware('role:Manager|Admin')->group(function () {
        Route::apiResource('/services', ServiceController::class);
        Route::get('/services/hall/{hallName}', [ServiceController::class, 'getServicesByHallName']);
        Route::post('/services/hall/{hallName}', [ServiceController::class, 'storeForHall']);

        Route::get('/venues/rated', [VenueController::class, 'getVenueRatings']);
        Route::get('/venues/hall/{hallId}', [VenueController::class, 'getVenuesByHallId']);

        Route::delete('/venues/{id}', [VenueController::class, 'destroy']);
        Route::post('/bookings', [BookingController::class, 'store']);
    });
});
