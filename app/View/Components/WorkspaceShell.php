<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class WorkspaceShell extends Component
{
    public function __construct(
        public string $title,
        public ?string $subtitle = null
    ) {
    }

    public function render(): View
    {
        $user = Auth::user();

        return view('components.workspace-shell', [
            'user' => $user,
            'navigation' => $this->navigation($user),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function navigation(mixed $user): array
    {
        $sections = [
            [
                'label' => 'Overview',
                'links' => [
                    $this->link(
                        'Role Dashboard',
                        'dashboard',
                        [
                            'dashboard',
                            'super-admin.dashboard',
                            'admin.dashboard',
                            'profiling-officer.dashboard',
                            'viewer.dashboard',
                        ],
                        'home'
                    ),
                    $this->link(
                        'Operations Overview',
                        'workspace.dashboard',
                        ['workspace.dashboard'],
                        'pulse'
                    ),
                    $this->link(
                        'Notifications',
                        'notifications.index',
                        ['notifications.*'],
                        'bell'
                    ),
                ],
            ],
            [
                'label' => 'Case Management',
                'links' => [
                    $this->link(
                        'Child Profiles',
                        'child-laborers.index',
                        ['child-laborers.*'],
                        'profiles'
                    ),
                    $this->link(
                        'Audit Schedules',
                        'audit-schedules.index',
                        ['audit-schedules.*'],
                        'audit'
                    ),
                ],
            ],
            [
                'label' => 'Information and Reports',
                'links' => [
                    $this->link(
                        'Master Reports',
                        'reports.child-laborers.index',
                        ['reports.child-laborers.*'],
                        'report'
                    ),
                    $this->link(
                        'Statistical Summary',
                        'reports.statistics.index',
                        ['reports.statistics.*'],
                        'chart'
                    ),
                ],
            ],
            [
                'label' => 'System Administration',
                'links' => [
                    $this->link(
                        'User Management',
                        'admin.users.index',
                        ['admin.users.*'],
                        'users'
                    ),
                    $this->link(
                        'Activity Logs',
                        'activity-logs.index',
                        ['activity-logs.*'],
                        'history'
                    ),
                    $this->link(
                        'Backup and Recovery',
                        'backups.index',
                        ['backups.*'],
                        'backup'
                    ),
                    $this->link(
                        'System Security',
                        'security.status',
                        ['security.*'],
                        'security'
                    ),
                    $this->link(
                        'Quality Assurance',
                        'quality-assurance.index',
                        ['quality-assurance.*'],
                        'quality'
                    ),
                ],
            ],
        ];

        return collect($sections)
            ->map(function (array $section) use ($user): array {
                $links = collect($section['links'])
                    ->filter(fn (array $link): bool =>
                        Route::has($link['route'])
                        && $this->canSeeLink($user, $link['route'])
                    )
                    ->values()
                    ->all();

                return [
                    ...$section,
                    'links' => $links,
                ];
            })
            ->filter(fn (array $section): bool => $section['links'] !== [])
            ->values()
            ->all();
    }

    /**
     * @param array<int, string> $patterns
     * @return array<string, mixed>
     */
    private function link(
        string $label,
        string $route,
        array $patterns,
        string $icon
    ): array {
        return compact('label', 'route', 'patterns', 'icon');
    }

    private function canSeeLink(mixed $user, string $routeName): bool
    {
        if (! $user) {
            return false;
        }

        if (in_array($routeName, [
            'audit-schedules.index',
            'admin.users.index',
            'activity-logs.index',
            'backups.index',
            'security.status',
            'quality-assurance.index',
        ], true)) {
            return $user->isSuperAdmin() || $user->isAdmin();
        }

        if (str_starts_with($routeName, 'reports.')) {
            return $user->isSuperAdmin()
                || $user->isAdmin()
                || $user->isViewer();
        }

        return true;
    }
}
