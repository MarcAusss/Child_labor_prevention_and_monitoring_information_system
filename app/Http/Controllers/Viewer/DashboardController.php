<?php

namespace App\Http\Controllers\Viewer;

use App\Http\Controllers\Controller;
use App\Models\ChildLaborer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $visibleProfiles = ChildLaborer::query()
            ->whereIn('status', [
                ChildLaborer::STATUS_SUBMITTED,
                ChildLaborer::STATUS_APPROVED,
            ]);

        return view('dashboards.viewer', [
            'user' => $request->user(),
            'visibleProfiles' => (clone $visibleProfiles)->count(),
            'approvedProfiles' => (clone $visibleProfiles)
                ->where('status', ChildLaborer::STATUS_APPROVED)
                ->count(),
            'submittedProfiles' => (clone $visibleProfiles)
                ->where('status', ChildLaborer::STATUS_SUBMITTED)
                ->count(),
            'recentProfiles' => (clone $visibleProfiles)
                ->latest('updated_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
