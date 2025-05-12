<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles  Comma-separated list of roles (e.g., "admin,worker")
     */
    public function handle(Request $request, Closure $next, string $roles): Response
{
    if (!auth()->check()) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

   $allowedRoles = array_map(fn($role) => strtolower(trim($role)), explode('|', $roles));
    $userRole = strtolower(auth()->user()->role->name);

    if (in_array($userRole, $allowedRoles)) {
        return $next($request);
    }

    return response()->json([
        'message' => 'Forbidden: You do not have the required role',
        'required_roles' => $allowedRoles,
        'your_role' => auth()->user()->role->name
    ], 403);
}}
