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
     * @return array<int, array{
     *     label:string,
     *     links:array<int, array{
     *         label:string,
     *         route:string,
     *         pattern:string,
     *         icon:string
     *     }>
     * }>
     */
    private function navigation(
        mixed $user
    ): array {
        $sections = [
            [
                'label' => 'Workspace',

                'links' => [
                    [
                        'label' =>
                            'Dashboard',

                        'route' =>
                            'workspace.dashboard',

                        'pattern' =>
                            'workspace.dashboard',

                        'icon' =>
                            'dashboard',
                    ],

                    [
                        'label' =>
                            'Child Profiles',

                        'route' =>
                            'child-laborers.index',

                        'pattern' =>
                            'child-laborers.*',

                        'icon' =>
                            'profiles',
                    ],

                    [
                        'label' =>
                            'Notifications',

                        'route' =>
                            'notifications.index',

                        'pattern' =>
                            'notifications.*',

                        'icon' =>
                            'bell',
                    ],
                ],
            ],

            [
                'label' => 'Operations',

                'links' => [
                    [
                        'label' =>
                            'Audit Schedules',

                        'route' =>
                            'audit-schedules.index',

                        'pattern' =>
                            'audit-schedules.*',

                        'icon' =>
                            'audit',
                    ],
                ],
            ],

            [
                'label' => 'Reports',

                'links' => [
                    [
                        'label' =>
                            'Master Reports',

                        'route' =>
                            'reports.child-laborers.index',

                        'pattern' =>
                            'reports.child-laborers.*',

                        'icon' =>
                            'report',
                    ],

                    [
                        'label' =>
                            'Statistics',

                        'route' =>
                            'reports.statistics.index',

                        'pattern' =>
                            'reports.statistics.*',

                        'icon' =>
                            'chart',
                    ],
                ],
            ],

            [
                'label' => 'Administration',

                'links' => [
                    [
                        'label' =>
                            'User Management',

                        'route' =>
                            'users.index',

                        'pattern' =>
                            'users.*',

                        'icon' =>
                            'users',
                    ],

                    [
                        'label' =>
                            'Activity Logs',

                        'route' =>
                            'activity-logs.index',

                        'pattern' =>
                            'activity-logs.*',

                        'icon' =>
                            'history',
                    ],
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

    private function canSeeLink(
        mixed $user,
        string $routeName
    ): bool {
        if (! $user) {
            return false;
        }

        if (
            $routeName
                === 'audit-schedules.index'
            || $routeName
                === 'activity-logs.index'
            || $routeName
                === 'users.index'
        ) {
            return $user->isSuperAdmin()
                || $user->isAdmin();
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
