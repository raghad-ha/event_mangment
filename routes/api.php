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
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

// Routes that require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Authenticated user info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ---------------- USER ROUTES ---------------- //
    Route::middleware('role:User')->group(function () {
        // Profile
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::get('/users', [UserController::class, 'index']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Bookings
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/users/{userId}/bookings', [UserController::class, 'showBookings']);
        Route::get('/users/{userId}/halls/{hallId}/bookings', [BookingController::class, 'showBookingsForUserInHall']);
        Route::post('/bookings/{bookingId}/review', [ReviewController::class, 'store']);
        Route::get('/users/{userId}/bookings', [BookingController::class, 'getBookingsByStatus']);

        // Search Halls and Venues
        Route::get('/halls/search', [HallController::class, 'showByName']);
        Route::get('/search-halls', [HallController::class, 'search']);
        Route::get('/show-specific-hall/{id}', [HallController::class, 'showSpecificHall']);
        Route::get('/venues/search', [VenueController::class, 'showByName']);
        Route::get('/venues/{venueId}', [VenueController::class, 'getVenueById']);
    });

    // ---------------- ADMIN ROUTES ---------------- //
    Route::middleware('role:Admin')->group(function () {
        // Admin dashboard (example)
        Route::get('/admin', function () {
            return 'Admin Page';
        });

        // Events
        Route::get('/events', [EventController::class, 'index']);
        Route::post('/events', [EventController::class, 'store']);
        Route::get('/events/{id}', [EventController::class, 'show']);
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);

        // Halls
        Route::post('/halls', [HallController::class, 'store']);
        Route::get('/halls', [HallController::class, 'index']);
        Route::get('/halls-with-venues-events', [HallController::class, 'getHallsWithVenuesAndEvents']);

        // Venues
        Route::post('/venues', [VenueController::class, 'store']);
        Route::get('/venues/rated', [VenueController::class, 'getVenueRatings']);
        Route::get('/venues/hall/{hallId}', [VenueController::class, 'getVenuesByHallId']);

        // Services
        Route::post('/services', [ServiceController::class, 'store']);
        Route::get('/services', [ServiceController::class, 'index']);
        Route::get('/services/{id}', [ServiceController::class, 'show']);
        Route::put('/services/{id}', [ServiceController::class, 'update']);
        Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

        // Bookings management
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
    });
});
