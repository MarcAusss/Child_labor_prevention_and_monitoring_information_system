<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboards.admin', [
            'totalUsers' => User::query()
                ->whereHas('role', function ($query): void {
                    $query->whereNot(
                        'slug',
                        Role::SUPER_ADMIN
                    );
                })
                ->count(),

            'activeUsers' => User::query()
                ->where('is_active', true)
                ->whereHas('role', function ($query): void {
                    $query->whereNot(
                        'slug',
                        Role::SUPER_ADMIN
                    );
                })
                ->count(),

            'profilingOfficers' => User::query()
                ->whereHas('role', function ($query): void {
                    $query->where(
                        'slug',
                        Role::PROFILING_OFFICER
                    );
                })
                ->count(),

            'viewers' => User::query()
                ->whereHas('role', function ($query): void {
                    $query->where(
                        'slug',
                        Role::VIEWER
                    );
                })
                ->count(),
        ]);
    }
}