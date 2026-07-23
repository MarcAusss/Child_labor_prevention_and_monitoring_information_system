<?php

namespace App\Http\Controllers;

use App\Http\Requests\Audit\AuditScheduleRequest;
use App\Models\AuditSchedule;
use App\Models\ChildLaborer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuditScheduleController extends Controller
{
    use AuthorizesRequests;

    public function index(
        Request $request
    ): View {
        $this->authorize(
            'viewAny',
            AuditSchedule::class
        );

        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $status = trim(
            (string) $request->query(
                'status',
                ''
            )
        );

        $assignedTo = $request->integer(
            'assigned_to'
        );

        $from = $this->validDate(
            $request->query('from')
        );

        $to = $this->validDate(
            $request->query('to')
        );

        $auditSchedules = AuditSchedule::query()
            ->with([
                'childLaborer:id,profile_number,first_name,middle_name,last_name,suffix,status',
                'creator:id,name,email',
                'assignedAdministrator:id,name,email',

                'latestEvaluation' => function ($query): void {
                    $query->select([
                        'audit_evaluations.id',
                        'audit_evaluations.audit_schedule_id',
                        'audit_evaluations.evaluation_date',
                        'audit_evaluations.status',
                    ]);
                },
            ])
            ->withCount('evaluations')
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(
                        function (Builder $query) use ($search): void {
                            $query
                                ->where(
                                    'location',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'remarks',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhereHas(
                                    'childLaborer',
                                    function (Builder $query) use ($search): void {
                                        $query
                                            ->where(
                                                'profile_number',
                                                'like',
                                                '%' . $search . '%'
                                            )
                                            ->orWhere(
                                                'first_name',
                                                'like',
                                                '%' . $search . '%'
                                            )
                                            ->orWhere(
                                                'last_name',
                                                'like',
                                                '%' . $search . '%'
                                            );
                                    }
                                );
                        }
                    );
                }
            )
            ->when(
                in_array(
                    $status,
                    AuditSchedule::statuses(),
                    true
                ),
                fn(Builder $query) =>
                $query->where(
                    'status',
                    $status
                )
            )
            ->when(
                $assignedTo > 0,
                fn(Builder $query) =>
                $query->where(
                    'assigned_to',
                    $assignedTo
                )
            )
            ->when(
                $from !== '',
                fn(Builder $query) =>
                $query->whereDate(
                    'scheduled_at',
                    '>=',
                    $from
                )
            )
            ->when(
                $to !== '',
                fn(Builder $query) =>
                $query->whereDate(
                    'scheduled_at',
                    '<=',
                    $to
                )
            )
            ->orderByRaw(
                "
                CASE status
                    WHEN 'In Progress' THEN 1
                    WHEN 'Scheduled' THEN 2
                    WHEN 'Completed' THEN 3
                    WHEN 'Cancelled' THEN 4
                    ELSE 5
                END
                "
            )
            ->orderBy('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        $eligibleAdministrators =
            $this->eligibleAdministrators();

        return view(
            'audit-schedules.index',
            compact(
                'auditSchedules',
                'eligibleAdministrators',
                'search',
                'status',
                'assignedTo',
                'from',
                'to'
            )
        );
    }

    public function create(
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'create',
            AuditSchedule::class
        );

        $this->ensureProfileCanBeScheduled(
            $childLaborer
        );

        return view(
            'audit-schedules.create',
            [
                'childLaborer' =>
                    $childLaborer,

                'auditSchedule' =>
                    null,

                'eligibleAdministrators' =>
                    $this->eligibleAdministrators(),

                'statuses' => [
                    AuditSchedule::STATUS_SCHEDULED,
                ],
            ]
        );
    }

    public function store(
        AuditScheduleRequest $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $this->authorize(
            'create',
            AuditSchedule::class
        );

        $this->ensureProfileCanBeScheduled(
            $childLaborer
        );

        $validated = $request->validated();

        $auditSchedule = $childLaborer
            ->auditSchedules()
            ->create([
                ...$validated,

                'created_by' =>
                    $request->user()->id,

                /*
                 * A newly created schedule always begins
                 * as Scheduled.
                 */
                'status' =>
                    AuditSchedule::STATUS_SCHEDULED,
            ]);

        return redirect()
            ->route(
                'audit-schedules.show',
                $auditSchedule
            )
            ->with(
                'success',
                'The audit schedule was created successfully.'
            );
    }

    public function show(
        AuditSchedule $auditSchedule
    ): View {
        $this->authorize(
            'view',
            $auditSchedule
        );

        $auditSchedule->load([
            'childLaborer',
            'creator:id,name,email',
            'assignedAdministrator:id,name,email',
            'evaluations' => fn($query) =>
                $query
                    ->with([
                        'evaluator:id,name,email',
                        'lastUpdater:id,name,email',
                    ])
                    ->orderByDesc('evaluation_date')
                    ->orderByDesc('id'),
        ]);

        return view(
            'audit-schedules.show',
            [
                'auditSchedule' =>
                    $auditSchedule,

                'evaluationStatuses' =>
                    \App\Models\AuditEvaluation::statuses(),
            ]
        );
    }

    public function edit(
        AuditSchedule $auditSchedule
    ): View {
        $this->authorize(
            'update',
            $auditSchedule
        );

        $this->ensureScheduleEditable(
            $auditSchedule
        );

        $auditSchedule->load(
            'childLaborer'
        );

        return view(
            'audit-schedules.edit',
            [
                'childLaborer' =>
                    $auditSchedule->childLaborer,

                'auditSchedule' =>
                    $auditSchedule,

                'eligibleAdministrators' =>
                    $this->eligibleAdministrators(),

                'statuses' =>
                    AuditSchedule::editableStatuses(),
            ]
        );
    }

    public function update(
        AuditScheduleRequest $request,
        AuditSchedule $auditSchedule
    ): RedirectResponse {
        $this->authorize(
            'update',
            $auditSchedule
        );

        $this->ensureScheduleEditable(
            $auditSchedule
        );

        $validated = $request->validated();

        DB::transaction(function () use ($auditSchedule, $validated): void {
            $status = $validated['status'];

            $values = [
                ...$validated,
            ];

            if (
                $status
                === AuditSchedule::STATUS_IN_PROGRESS
            ) {
                $values['started_at'] =
                    $auditSchedule->started_at
                    ?: now();

                $values['cancelled_at'] =
                    null;
            }

            if (
                $status
                === AuditSchedule::STATUS_CANCELLED
            ) {
                $values['cancelled_at'] =
                    $auditSchedule->cancelled_at
                    ?: now();
            }

            if (
                $status
                === AuditSchedule::STATUS_SCHEDULED
            ) {
                $values['cancelled_at'] =
                    null;
            }

            $auditSchedule->update(
                $values
            );
        });

        return redirect()
            ->route(
                'audit-schedules.show',
                $auditSchedule
            )
            ->with(
                'success',
                'The audit schedule was updated successfully.'
            );
    }

    private function eligibleAdministrators(): Collection
    {
        return User::query()
            ->with('role')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->filter(
                fn(User $user): bool =>
                $user->isAdmin()
                || $user->isSuperAdmin()
            )
            ->values();
    }

    private function ensureProfileCanBeScheduled(
        ChildLaborer $childLaborer
    ): void {
        if (
            in_array(
                $childLaborer->status,
                [
                    ChildLaborer::STATUS_SUBMITTED,
                    ChildLaborer::STATUS_APPROVED,
                ],
                true
            )
        ) {
            return;
        }

        throw ValidationException::withMessages([
            'child_laborer' =>
                'Only Submitted or Approved child laborer profiles can receive an audit schedule.',
        ]);
    }

    private function ensureScheduleEditable(
        AuditSchedule $auditSchedule
    ): void {
        if ($auditSchedule->isEditable()) {
            return;
        }

        throw ValidationException::withMessages([
            'status' =>
                'A completed or cancelled audit schedule can no longer be edited.',
        ]);
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