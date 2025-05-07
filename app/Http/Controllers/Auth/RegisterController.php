<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
class RegisterController extends Controller
{
    // /*
    // |--------------------------------------------------------------------------
    // | Register Controller
    // |--------------------------------------------------------------------------
    // |
    // | This controller handles the registration of new users as well as their
    // | validation and creation. By default this controller uses a trait to
    // | provide this functionality without requiring any additional code.
    // |
    // */



    public function register(Request $request)
    {
        // Manually handle the incoming request data
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $password_confirmation = $request->input('password_confirmation');
        $role_id = $request->input('role_id');
        $hall_id = $request->input('hall_id');

        // Check if password and password_confirmation match
        if ($password !== $password_confirmation) {
            return response()->json(['message' => 'Passwords do not match'], 400);
        }

        // Hash the password
        $hashedPassword = Hash::make($password);

        // If the role is Manager, ensure hall_id is provided
        if ($role_id == 2 && !$hall_id) {  // Assuming '2' is Manager
            return response()->json(['message' => 'Manager must have a hall_id'], 400);
        }

        // Create the user
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role_id' => $role_id,
                'hall_id' => $hall_id ?? null,  // Assign hall_id only if provided (for Manager)
            ]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during registration.'], 500);
        }
    }}
