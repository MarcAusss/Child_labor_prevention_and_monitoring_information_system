<?php

namespace App\Http\Controllers;

use App\Services\QualityAssurance\QualityAssuranceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QualityAssuranceController extends Controller
{
    public function __invoke(
        Request $request,
        QualityAssuranceService $qualityAssuranceService
    ): View {
        $user = $request->user();

        abort_unless(
            $user
            && (
                $user->isSuperAdmin()
                || $user->isAdmin()
            ),
            403
        );

        return view(
            'quality-assurance.index',
            [
                'reports' =>
                    $qualityAssuranceService
                        ->recentReports(),
            ]
        );
    }
}
