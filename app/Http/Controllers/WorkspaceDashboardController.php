<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\WorkspaceDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceDashboardController extends Controller
{
    public function __construct(
        private readonly WorkspaceDashboardService
            $dashboardService
    ) {
    }

    public function __invoke(
        Request $request
    ): View {
        return view(
            'dashboard.workspace',
            $this->dashboardService
                ->build(
                    $request->user()
                )
        );
    }
}
