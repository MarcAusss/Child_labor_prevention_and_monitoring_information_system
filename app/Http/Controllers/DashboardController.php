<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match ($user->role?->slug) {
            Role::SUPER_ADMIN => redirect()->route(
                'super-admin.dashboard'
            ),

            Role::ADMIN => redirect()->route(
                'admin.dashboard'
            ),

            Role::PROFILING_OFFICER => redirect()->route(
                'profiling-officer.dashboard'
            ),

            Role::VIEWER => redirect()->route(
                'viewer.dashboard'
            ),

            default => abort(
                403,
                'Your account does not have a recognized system role.'
            ),
        };
    }
}