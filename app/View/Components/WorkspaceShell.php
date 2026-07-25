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

        return view(
            'components.workspace-shell',
            [
                'user' => $user,

                'navigation' =>
                    $this->navigation($user),
            ]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function navigation(
        mixed $user
    ): array {
        $sections = [
            [
                'label' => 'Workspace',

                'links' => [
                    $this->link(
                        'Dashboard',
                        'workspace.dashboard',
                        'workspace.dashboard',
                        'dashboard'
                    ),

                    $this->link(
                        'Child Profiles',
                        'child-laborers.index',
                        'child-laborers.*',
                        'profiles'
                    ),

                    $this->link(
                        'Import Spreadsheet',
                        'child-laborers.import.index',
                        'child-laborers.import.*',
                        'report'
                    ),

                    $this->link(
                        'Notifications',
                        'notifications.index',
                        'notifications.*',
                        'bell'
                    ),
                ],
            ],

            [
                'label' => 'Operations',

                'links' => [
                    $this->link(
                        'Audit Schedules',
                        'audit-schedules.index',
                        'audit-schedules.*',
                        'audit'
                    ),
                ],
            ],

            [
                'label' => 'Reports',

                'links' => [
                    $this->link(
                        'Master Reports',
                        'reports.child-laborers.index',
                        'reports.child-laborers.*',
                        'report'
                    ),

                    $this->link(
                        'Statistics',
                        'reports.statistics.index',
                        'reports.statistics.*',
                        'chart'
                    ),
                ],
            ],

            [
                'label' => 'Administration',

                'links' => [
                    $this->link(
                        'User Management',
                        'users.index',
                        'users.*',
                        'users'
                    ),

                    $this->link(
                        'Activity Logs',
                        'activity-logs.index',
                        'activity-logs.*',
                        'history'
                    ),

                    $this->link(
                        'Backup and Recovery',
                        'backups.index',
                        'backups.*',
                        'history'
                    ),

                    $this->link(
                        'System Security',
                        'security.status',
                        'security.*',
                        'security'
                    ),

                    $this->link(
                        'Quality Assurance',
                        'quality-assurance.index',
                        'quality-assurance.*',
                        'chart'
                    ),
                ],
            ],
        ];

        return collect($sections)
            ->map(
                function (
                    array $section
                ) use ($user): array {
                    $links = collect(
                        $section['links']
                    )
                        ->filter(
                            fn (
                                array $link
                            ): bool =>
                                Route::has(
                                    $link['route']
                                )
                                && $this
                                    ->canSeeLink(
                                        $user,
                                        $link['route']
                                    )
                        )
                        ->values()
                        ->all();

                    return [
                        ...$section,
                        'links' => $links,
                    ];
                }
            )
            ->filter(
                fn (array $section): bool =>
                    $section['links'] !== []
            )
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function link(
        string $label,
        string $route,
        string $pattern,
        string $icon
    ): array {
        return [
            'label' => $label,
            'route' => $route,
            'pattern' => $pattern,
            'patterns' => [$pattern],
            'icon' => $icon,
        ];
    }

    private function canSeeLink(
        mixed $user,
        string $routeName
    ): bool {
        if (! $user) {
            return false;
        }

        if (
            in_array(
                $routeName,
                [
                    'audit-schedules.index',
                    'activity-logs.index',
                    'users.index',
                    'security.status',
                    'backups.index',
                    'quality-assurance.index',
                ],
                true
            )
        ) {
            return $user->isSuperAdmin()
                || $user->isAdmin();
        }

        if ($routeName === 'child-laborers.import.index') {
            return $user->canImportChildLaborers();
        }

        if (
            str_starts_with(
                $routeName,
                'reports.'
            )
        ) {
            return $user->isSuperAdmin()
                || $user->isAdmin()
                || $user->isViewer();
        }

        return true;
    }
}
