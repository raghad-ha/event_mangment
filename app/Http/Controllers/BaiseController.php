<?php

namespace App\Http\Controllers;
use App\Models\Hall;
use Illuminate\Http\Request;

class BaiseController extends Controller
{
    protected function isManagerAndUnauthorized($hallName)
    {

        $manager = auth()->user()->load('role');
return $manager->role->name;
        if ($manager->role->name === 'Admin') {
            return false; // Admins are authorized for all halls
        }

        // If the user is a Manager, check if they have access to this specific hall
        $hall = Hall::where('name', $hallName)->first();

        // If hall is not found or the hall doesn't belong to the manager, return true (unauthorized)
        if (!$hall || $hall->id !== $manager->hall_id) {
            return true; // Unauthorized access for Manager
        }

        // If the hall exists and belongs to the manager, return false (authorized)
        return false;
    }

    /**
     * Check if the current authenticated user is a Manager and if they are
     * trying to access a hall they are not assigned to using hall ID.
     *
     * @param int $hallId
     * @return bool
     */
    protected function isManagerAndUnauthorizedById($hallId)
    {


        $manager = auth()->user(); // Get the currently authenticated user

        // If the user is an Admin, return true (Admin has access to everything)
        if ($manager->role->name === 'Admin') {
            return false; // Admins are authorized for all halls
        }

        // If the user is a Manager, check if they have access to this specific hall
        $hall = Hall::find($hallId);

        // If hall doesn't exist or the hall doesn't belong to the manager, return true (unauthorized)
        if (!$hall || $hall->id !== $manager->hall_id) {
            return true; // Unauthorized access for Manager
        }

        // If the hall exists and belongs to the manager, return false (authorized)
        return false;
    }
}
