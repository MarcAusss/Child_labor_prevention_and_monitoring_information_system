<?php

namespace App\Http\Controllers;

use App\Services\Security\SecurityAuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSecurityController extends Controller
{
    public function __invoke(
        Request $request,
        SecurityAuditService $securityAuditService
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
            'security.status',
            $securityAuditService
                ->audit()
        );
    }
}
