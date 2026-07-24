<?php

namespace App\Http\Controllers\ProfilingOfficer;

use App\Http\Controllers\Controller;
use App\Models\ChildLaborer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $profiles = ChildLaborer::query()
            ->where(function (Builder $query) use ($user): void {
                $query
                    ->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id);
            });

        return view('dashboards.profiling-officer', [
            'user' => $user,
            'totalProfiles' => (clone $profiles)->count(),
            'draftProfiles' => (clone $profiles)
                ->where('status', ChildLaborer::STATUS_DRAFT)
                ->count(),
            'submittedProfiles' => (clone $profiles)
                ->where('status', ChildLaborer::STATUS_SUBMITTED)
                ->count(),
            'returnedProfiles' => (clone $profiles)
                ->where('status', ChildLaborer::STATUS_RETURNED)
                ->count(),
            'approvedProfiles' => (clone $profiles)
                ->where('status', ChildLaborer::STATUS_APPROVED)
                ->count(),
            'recentProfiles' => (clone $profiles)
                ->latest('updated_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
