<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditSchedule;
use App\Models\ChildLaborer;
use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboards.super-admin', [
            'totalUsers' => User::query()->count(),
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'inactiveUsers' => User::query()->where('is_active', false)->count(),
            'totalRoles' => Role::query()->where('is_active', true)->count(),
            'totalProfiles' => ChildLaborer::query()->count(),
            'submittedProfiles' => ChildLaborer::query()
                ->where('status', ChildLaborer::STATUS_SUBMITTED)
                ->count(),
            'upcomingAudits' => AuditSchedule::query()->upcoming()->count(),
            'recentUsers' => User::query()
                ->with('role')
                ->latest()
                ->limit(5)
                ->get(),
            'recentProfiles' => ChildLaborer::query()
                ->with('assignedOfficer:id,name,email')
                ->latest('updated_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
