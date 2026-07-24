<?php

namespace App\Services\Dashboard;

use App\Models\ActivityLog;
use App\Models\AuditSchedule;
use App\Models\ChildLaborer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WorkspaceDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $profiles = $this->profileQuery($user);

        $profileIds = (
            clone $profiles
        )->pluck('child_laborers.id');

        return [
            'roleLabel' =>
                $this->roleLabel($user),

            'summary' => [
                'total_profiles' =>
                    (clone $profiles)->count(),

                'draft_profiles' =>
                    (clone $profiles)
                        ->where(
                            'status',
                            ChildLaborer::STATUS_DRAFT
                        )
                        ->count(),

                'submitted_profiles' =>
                    (clone $profiles)
                        ->where(
                            'status',
                            ChildLaborer::STATUS_SUBMITTED
                        )
                        ->count(),

                'returned_profiles' =>
                    (clone $profiles)
                        ->where(
                            'status',
                            ChildLaborer::STATUS_RETURNED
                        )
                        ->count(),

                'approved_profiles' =>
                    (clone $profiles)
                        ->where(
                            'status',
                            ChildLaborer::STATUS_APPROVED
                        )
                        ->count(),

                'archived_profiles' =>
                    (clone $profiles)
                        ->where(
                            'status',
                            ChildLaborer::STATUS_ARCHIVED
                        )
                        ->count(),

                'currently_working' =>
                    (clone $profiles)
                        ->whereHas(
                            'currentEmployment'
                        )
                        ->count(),

                'with_interventions' =>
                    (clone $profiles)
                        ->whereHas(
                            'interventions'
                        )
                        ->count(),

                'unread_notifications' =>
                    $user
                        ->unreadNotifications()
                        ->count(),
            ],

            'statusDistribution' =>
                $this->statusDistribution(
                    $profiles
                ),

            'monthlyTrend' =>
                $this->monthlyTrend(
                    $profiles
                ),

            'recentProfiles' =>
                $this->recentProfiles(
                    $profiles
                ),

            'upcomingAudits' =>
                $this->upcomingAudits(
                    $user,
                    $profileIds
                ),

            'recentActivity' =>
                $this->recentActivity(
                    $user,
                    $profileIds
                ),

            'recentNotifications' =>
                $user
                    ->notifications()
                    ->latest()
                    ->limit(5)
                    ->get(),
        ];
    }

    /**
     * @return Builder<ChildLaborer>
     */
    public function profileQuery(
        User $user
    ): Builder {
        $query = ChildLaborer::query();

        if ($user->isProfilingOfficer()) {
            return $query->where(
                function (
                    Builder $query
                ) use ($user): void {
                    $query
                        ->where(
                            'created_by',
                            $user->id
                        )
                        ->orWhere(
                            'assigned_to',
                            $user->id
                        );
                }
            );
        }

        if ($user->isViewer()) {
            return $query->whereIn(
                'status',
                [
                    ChildLaborer::STATUS_SUBMITTED,
                    ChildLaborer::STATUS_APPROVED,
                ]
            );
        }

        return $query;
    }

    /**
     * @param Builder<ChildLaborer> $profiles
     *
     * @return Collection<int, array{
     *     label:string,
     *     total:int,
     *     percentage:float
     * }>
     */
    private function statusDistribution(
        Builder $profiles
    ): Collection {
        $statuses = [
            ChildLaborer::STATUS_DRAFT,
            ChildLaborer::STATUS_SUBMITTED,
            ChildLaborer::STATUS_RETURNED,
            ChildLaborer::STATUS_APPROVED,
            ChildLaborer::STATUS_ARCHIVED,
        ];

        $counts = (
            clone $profiles
        )
            ->selectRaw(
                'status, COUNT(*) AS total'
            )
            ->groupBy('status')
            ->pluck(
                'total',
                'status'
            );

        $total = (int) $counts->sum();

        return collect($statuses)
            ->map(
                function (
                    string $status
                ) use (
                    $counts,
                    $total
                ): array {
                    $count = (int) (
                        $counts[$status]
                        ?? 0
                    );

                    return [
                        'label' => $status,

                        'total' => $count,

                        'percentage' =>
                            $total > 0
                                ? round(
                                    ($count / $total)
                                    * 100,
                                    2
                                )
                                : 0.0,
                    ];
                }
            );
    }

    /**
     * @param Builder<ChildLaborer> $profiles
     *
     * @return Collection<int, array{
     *     label:string,
     *     total:int,
     *     percentage:float
     * }>
     */
    private function monthlyTrend(
        Builder $profiles
    ): Collection {
        $months = collect(
            range(5, 0)
        )->map(
            fn (int $monthsAgo): Carbon =>
                now()
                    ->startOfMonth()
                    ->subMonths($monthsAgo)
        );

        $records = (
            clone $profiles
        )
            ->where(
                'created_at',
                '>=',
                $months->first()
                    ->copy()
                    ->startOfMonth()
            )
            ->get([
                'created_at',
            ])
            ->groupBy(
                fn (ChildLaborer $profile): string =>
                    $profile
                        ->created_at
                        ->format('Y-m')
            )
            ->map(
                fn ($items): int =>
                    $items->count()
            );

        $maximum = max(
            1,
            (int) $records->max()
        );

        return $months->map(
            function (
                Carbon $month
            ) use (
                $records,
                $maximum
            ): array {
                $total = (int) (
                    $records[
                        $month->format('Y-m')
                    ] ?? 0
                );

                return [
                    'label' =>
                        $month->format('M Y'),

                    'total' =>
                        $total,

                    'percentage' =>
                        round(
                            ($total / $maximum)
                            * 100,
                            2
                        ),
                ];
            }
        );
    }

    /**
     * @param Builder<ChildLaborer> $profiles
     *
     * @return Collection<int, ChildLaborer>
     */
    private function recentProfiles(
        Builder $profiles
    ): Collection {
        return (
            clone $profiles
        )
            ->with([
                'assignedOfficer:id,name,email',
                'residentialAddress.province:id,name',
                'residentialAddress.locality:id,name',
            ])
            ->latest('updated_at')
            ->limit(6)
            ->get();
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, AuditSchedule>
     */
    private function upcomingAudits(
        User $user,
        Collection $profileIds
    ): Collection {
        if (
            ! $user->isSuperAdmin()
            && ! $user->isAdmin()
        ) {
            return collect();
        }

        if ($profileIds->isEmpty()) {
            return collect();
        }

        return AuditSchedule::query()
            ->whereIn(
                'child_laborer_id',
                $profileIds
            )
            ->whereIn(
                'status',
                [
                    AuditSchedule::STATUS_SCHEDULED,
                    AuditSchedule::STATUS_IN_PROGRESS,
                ]
            )
            ->where(
                'scheduled_at',
                '>=',
                now()->startOfDay()
            )
            ->with([
                'childLaborer:id,profile_number,first_name,middle_name,last_name,suffix',
                'assignedAdministrator:id,name,email',
            ])
            ->orderBy('scheduled_at')
            ->limit(6)
            ->get();
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, ActivityLog>
     */
    private function recentActivity(
        User $user,
        Collection $profileIds
    ): Collection {
        if ($user->isViewer()) {
            return collect();
        }

        $query = ActivityLog::query()
            ->with([
                'actor:id,name,email',
                'childLaborer:id,profile_number,first_name,middle_name,last_name,suffix',
            ]);

        if ($user->isProfilingOfficer()) {
            if ($profileIds->isEmpty()) {
                return collect();
            }

            $query->whereIn(
                'child_laborer_id',
                $profileIds
            );
        }

        return $query
            ->latest('created_at')
            ->limit(6)
            ->get();
    }

    private function roleLabel(
        User $user
    ): string {
        return match (true) {
            $user->isSuperAdmin() =>
                'Super Administrator',

            $user->isAdmin() =>
                'Administrator',

            $user->isProfilingOfficer() =>
                'Profiling Officer',

            $user->isViewer() =>
                'Viewer',

            default =>
                $user->role?->name
                ?: 'Authorized User',
        };
    }
}
