<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ChildLaborer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    use AuthorizesRequests;

    public function index(
        Request $request
    ): View {
        $this->authorize(
            'viewAny',
            ActivityLog::class
        );

        $filters = $this->filters(
            $request
        );

        $logs = $this->applyFilters(
            ActivityLog::query()
                ->with([
                    'actor:id,name,email',
                    'childLaborer:id,profile_number,first_name,last_name',
                ]),
            $filters
        )
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $actions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $entityTypes = ActivityLog::query()
            ->whereNotNull('entity_type')
            ->select('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');

        $users = User::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);

        $todayCount = ActivityLog::query()
            ->whereDate(
                'created_at',
                today()
            )
            ->count();

        $lastSevenDaysCount =
            ActivityLog::query()
                ->where(
                    'created_at',
                    '>=',
                    now()->subDays(7)
                )
                ->count();

        $failedLoginCount =
            ActivityLog::query()
                ->where(
                    'action',
                    ActivityLog::ACTION_LOGIN_FAILED
                )
                ->count();

        return view(
            'activity-logs.index',
            compact(
                'logs',
                'actions',
                'entityTypes',
                'users',
                'filters',
                'todayCount',
                'lastSevenDaysCount',
                'failedLoginCount'
            )
        );
    }

    public function show(
        ActivityLog $activityLog
    ): View {
        $this->authorize(
            'view',
            $activityLog
        );

        $activityLog->load([
            'actor:id,name,email',
            'childLaborer:id,profile_number,first_name,last_name',
        ]);

        return view(
            'activity-logs.show',
            compact('activityLog')
        );
    }

    public function profile(
        Request $request,
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'viewActivity',
            $childLaborer
        );

        $filters = $this->filters(
            $request
        );

        $logs = $this->applyFilters(
            $childLaborer
                ->activityLogs()
                ->with([
                    'actor:id,name,email',
                ]),
            $filters,
            allowUserFilter: false,
            allowEntityFilter: false
        )
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $actions = $childLaborer
            ->activityLogs()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view(
            'child-laborers.activity-logs.index',
            compact(
                'childLaborer',
                'logs',
                'actions',
                'filters'
            )
        );
    }

    /**
     * @return array{
     *     search: string,
     *     action: string,
     *     entity_type: string,
     *     user_id: int|null,
     *     from: string,
     *     to: string
     * }
     */
    private function filters(
        Request $request
    ): array {
        $userId = $request->integer(
            'user_id'
        );

        return [
            'search' => trim(
                (string) $request->query(
                    'search',
                    ''
                )
            ),

            'action' => trim(
                (string) $request->query(
                    'action',
                    ''
                )
            ),

            'entity_type' => trim(
                (string) $request->query(
                    'entity_type',
                    ''
                )
            ),

            'user_id' =>
                $userId > 0
                    ? $userId
                    : null,

            'from' => $this->validDate(
                $request->query('from')
            ),

            'to' => $this->validDate(
                $request->query('to')
            ),
        ];
    }

    /**
     * @param Builder<ActivityLog> $query
     * @param array<string, mixed> $filters
     *
     * @return Builder<ActivityLog>
     */
    private function applyFilters(
        Builder $query,
        array $filters,
        bool $allowUserFilter = true,
        bool $allowEntityFilter = true
    ): Builder {
        return $query
            ->when(
                $filters['search'] !== '',
                function (
                    Builder $query
                ) use ($filters): void {
                    $search =
                        $filters['search'];

                    $query->where(
                        function (
                            Builder $query
                        ) use ($search): void {
                            $query
                                ->where(
                                    'description',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhere(
                                    'actor_name',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhere(
                                    'role_name',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhere(
                                    'action',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhereHas(
                                    'childLaborer',
                                    function (
                                        Builder $query
                                    ) use ($search): void {
                                        $query
                                            ->where(
                                                'profile_number',
                                                'like',
                                                '%'.$search.'%'
                                            )
                                            ->orWhere(
                                                'first_name',
                                                'like',
                                                '%'.$search.'%'
                                            )
                                            ->orWhere(
                                                'last_name',
                                                'like',
                                                '%'.$search.'%'
                                            );
                                    }
                                );
                        }
                    );
                }
            )
            ->when(
                $filters['action'] !== '',
                fn (Builder $query) =>
                    $query->where(
                        'action',
                        $filters['action']
                    )
            )
            ->when(
                $allowEntityFilter
                && $filters['entity_type'] !== '',
                fn (Builder $query) =>
                    $query->where(
                        'entity_type',
                        $filters['entity_type']
                    )
            )
            ->when(
                $allowUserFilter
                && $filters['user_id'],
                fn (Builder $query) =>
                    $query->where(
                        'user_id',
                        $filters['user_id']
                    )
            )
            ->when(
                $filters['from'] !== '',
                fn (Builder $query) =>
                    $query->whereDate(
                        'created_at',
                        '>=',
                        $filters['from']
                    )
            )
            ->when(
                $filters['to'] !== '',
                fn (Builder $query) =>
                    $query->whereDate(
                        'created_at',
                        '<=',
                        $filters['to']
                    )
            );
    }

    private function validDate(
        mixed $value
    ): string {
        $value = trim(
            (string) $value
        );

        return preg_match(
            '/^\d{4}-\d{2}-\d{2}$/',
            $value
        ) === 1
            ? $value
            : '';
    }
}