<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaiseController;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{

public function index()
{
    $users = User::with(['role', 'hall'])->get(); // تحميل العلاقات مسبقًا

    // تعديل الـ response لاستخراج الأسماء فقط
    $users->transform(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role_name' => $user->role->name ?? null, // استخدام العلاقة
            'hall_name' => $user->hall->name ?? null,
            // ... أضف حقول أخرى حسب الحاجة
        ];
    });

    return response()->json($users);
}

    public function show($id)
    {
        // Find the user by id
       $user= Auth::id();

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Return the user's information
        return response()->json($user);
    }

    // Update user information by id
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ]);

        // Find the user by id
        //$userId = Auth::id();
        $user = User::find($id);


        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Update the user's information
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        // Save the updated user
        $user->save();

        // Return the updated user information
        return response()->json($user);
    }

    // Delete user by id
    public function destroy($id)
    {
        // Find the user by id
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Delete the user
        $user->delete();

        // Return a success message
        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function showBookings($userId)
    {
        // Find the user by ID
        $user = User::find($userId);

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Get the user's bookings
        $bookings = $user->bookings;

        // If no bookings found, return a message
        if ($bookings->isEmpty()) {
            return response()->json(['message' => 'No bookings found for this user.'], 404);
        }

        // Return the user's bookings
        return response()->json($bookings);
    }
   public function profile(Request $request)
{
    
    // Get user ID from request (if needed)

    $userId = $request->input('user_id');

    // Fetch user without requiring authentication
    $user = User::with(['role', 'hall'])->find($userId);

    // If user is not found, return an error response
    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    // Build response data
    $profileData = [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role_name' => $user->role->name ?? 'No assigned role',
        'hall_name' => $user->hall->name ?? 'No assigned hall',
        'created_at' => $user->created_at->format('Y-m-d H:i:s'),
        'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
    ];

    return response()->json($profileData, 200);
}

}
