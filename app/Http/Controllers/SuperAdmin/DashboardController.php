<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboards.super-admin', [
            'totalUsers' => User::query()->count(),

            'activeUsers' => User::query()
                ->where('is_active', true)
                ->count(),

            'inactiveUsers' => User::query()
                ->where('is_active', false)
                ->count(),

            'totalRoles' => Role::query()
                ->where('is_active', true)
                ->count(),

            'recentUsers' => User::query()
                ->with('role')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}