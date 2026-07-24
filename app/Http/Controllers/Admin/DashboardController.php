<?php

namespace App\Http\Controllers\Admin;

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
        $nonSuperAdminUsers = User::query()
            ->whereHas('role', function ($query): void {
                $query->whereNot('slug', Role::SUPER_ADMIN);
            });

        return view('dashboards.admin', [
            'totalUsers' => (clone $nonSuperAdminUsers)->count(),
            'activeUsers' => (clone $nonSuperAdminUsers)
                ->where('is_active', true)
                ->count(),
            'profilingOfficers' => User::query()
                ->whereHas('role', fn ($query) => $query->where('slug', Role::PROFILING_OFFICER))
                ->count(),
            'viewers' => User::query()
                ->whereHas('role', fn ($query) => $query->where('slug', Role::VIEWER))
                ->count(),
            'totalProfiles' => ChildLaborer::query()->count(),
            'submittedProfiles' => ChildLaborer::query()
                ->where('status', ChildLaborer::STATUS_SUBMITTED)
                ->count(),
            'approvedProfiles' => ChildLaborer::query()
                ->where('status', ChildLaborer::STATUS_APPROVED)
                ->count(),
            'upcomingAudits' => AuditSchedule::query()->upcoming()->count(),
            'recentProfiles' => ChildLaborer::query()
                ->with('assignedOfficer:id,name,email')
                ->latest('updated_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
