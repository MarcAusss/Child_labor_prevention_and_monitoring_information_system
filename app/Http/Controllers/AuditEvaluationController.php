<?php

namespace App\Http\Controllers;

use App\Http\Requests\Audit\AuditEvaluationRequest;
use App\Models\AuditEvaluation;
use App\Models\AuditSchedule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuditEvaluationController extends Controller
{
    use AuthorizesRequests;

    public function store(
        AuditEvaluationRequest $request,
        AuditSchedule $auditSchedule
    ): RedirectResponse {
        $this->authorize(
            'create',
            [
                AuditEvaluation::class,
                $auditSchedule,
            ]
        );

        $this->ensureScheduleAcceptsEvaluations(
            $auditSchedule
        );

        $validated = $request->validated();

        $this->ensureFinalEvaluationIsUnique(
            $auditSchedule,
            $validated['status']
        );

        DB::transaction(function () use (
            $request,
            $auditSchedule,
            $validated
        ): void {
            $timestamps = $this
                ->statusTimestamps(
                    $validated['status']
                );

            $auditSchedule
                ->evaluations()
                ->create([
                    ...$validated,

                    'evaluated_by' =>
                        $request->user()->id,

                    'updated_by' =>
                        $request->user()->id,

                    ...$timestamps,
                ]);

            $this->synchronizeScheduleStatus(
                $auditSchedule
            );
        });

        return back()->with(
            'success',
            'The audit evaluation was added successfully.'
        );
    }

    public function edit(
        AuditSchedule $auditSchedule,
        AuditEvaluation $auditEvaluation
    ): View {
        $this->ensureBelongsToSchedule(
            $auditSchedule,
            $auditEvaluation
        );

        $this->authorize(
            'update',
            $auditEvaluation
        );

        $this->ensureEvaluationEditable(
            $auditEvaluation
        );

        $auditSchedule->load([
            'childLaborer',
        ]);

        return view(
            'audit-evaluations.edit',
            [
                'auditSchedule' =>
                    $auditSchedule,

                'auditEvaluation' =>
                    $auditEvaluation,

                'evaluationStatuses' =>
                    AuditEvaluation::statuses(),
            ]
        );
    }

    public function update(
        AuditEvaluationRequest $request,
        AuditSchedule $auditSchedule,
        AuditEvaluation $auditEvaluation
    ): RedirectResponse {
        $this->ensureBelongsToSchedule(
            $auditSchedule,
            $auditEvaluation
        );

        $this->authorize(
            'update',
            $auditEvaluation
        );

        $this->ensureEvaluationEditable(
            $auditEvaluation
        );

        $validated = $request->validated();

        $this->ensureFinalEvaluationIsUnique(
            $auditSchedule,
            $validated['status'],
            $auditEvaluation
        );

        DB::transaction(function () use (
            $request,
            $auditSchedule,
            $auditEvaluation,
            $validated
        ): void {
            $timestamps = $this
                ->statusTimestamps(
                    $validated['status'],
                    $auditEvaluation
                );

            $auditEvaluation->update([
                ...$validated,

                'updated_by' =>
                    $request->user()->id,

                ...$timestamps,
            ]);

            $this->synchronizeScheduleStatus(
                $auditSchedule
            );
        });

        return redirect()
            ->route(
                'audit-schedules.show',
                $auditSchedule
            )
            ->with(
                'success',
                'The audit evaluation was updated successfully.'
            );
    }

    private function synchronizeScheduleStatus(
        AuditSchedule $auditSchedule
    ): void {
        $auditSchedule->refresh();

        $hasFinalEvaluation =
            $auditSchedule
                ->evaluations()
                ->where(
                    'status',
                    AuditEvaluation::STATUS_FINALIZED
                )
                ->exists();

        if ($hasFinalEvaluation) {
            $auditSchedule->update([
                'status' =>
                    AuditSchedule::STATUS_COMPLETED,

                'started_at' =>
                    $auditSchedule->started_at
                    ?: now(),

                'completed_at' =>
                    $auditSchedule->completed_at
                    ?: now(),

                'cancelled_at' =>
                    null,
            ]);

            return;
        }

        $hasEvaluations =
            $auditSchedule
                ->evaluations()
                ->exists();

        if ($hasEvaluations) {
            $auditSchedule->update([
                'status' =>
                    AuditSchedule::STATUS_IN_PROGRESS,

                'started_at' =>
                    $auditSchedule->started_at
                    ?: now(),

                'completed_at' =>
                    null,

                'cancelled_at' =>
                    null,
            ]);
        }
    }

    /**
     * @return array{
     *     submitted_at: mixed,
     *     finalized_at: mixed
     * }
     */
    private function statusTimestamps(
        string $status,
        ?AuditEvaluation $evaluation = null
    ): array {
        return match ($status) {
            AuditEvaluation::STATUS_SUBMITTED => [
                'submitted_at' =>
                    $evaluation?->submitted_at
                    ?: now(),

                'finalized_at' =>
                    null,
            ],

            AuditEvaluation::STATUS_FINALIZED => [
                'submitted_at' =>
                    $evaluation?->submitted_at
                    ?: now(),

                'finalized_at' =>
                    $evaluation?->finalized_at
                    ?: now(),
            ],

            default => [
                'submitted_at' =>
                    null,

                'finalized_at' =>
                    null,
            ],
        };
    }

    private function ensureFinalEvaluationIsUnique(
        AuditSchedule $auditSchedule,
        string $status,
        ?AuditEvaluation $ignoredEvaluation = null
    ): void {
        if (
            $status
            !== AuditEvaluation::STATUS_FINALIZED
        ) {
            return;
        }

        $exists = $auditSchedule
            ->evaluations()
            ->where(
                'status',
                AuditEvaluation::STATUS_FINALIZED
            )
            ->when(
                $ignoredEvaluation,
                fn ($query) =>
                    $query->where(
                        'id',
                        '!=',
                        $ignoredEvaluation->id
                    )
            )
            ->exists();

        if (! $exists) {
            return;
        }

        throw ValidationException::withMessages([
            'status' =>
                'This audit schedule already has a finalized evaluation.',
        ]);
    }

    private function ensureScheduleAcceptsEvaluations(
        AuditSchedule $auditSchedule
    ): void {
        if ($auditSchedule->isEditable()) {
            return;
        }

        throw ValidationException::withMessages([
            'status' =>
                'A completed or cancelled schedule cannot receive another evaluation.',
        ]);
    }

    private function ensureEvaluationEditable(
        AuditEvaluation $auditEvaluation
    ): void {
        if ($auditEvaluation->isEditable()) {
            return;
        }

        throw ValidationException::withMessages([
            'status' =>
                'A finalized audit evaluation can no longer be edited.',
        ]);
    }

    private function ensureBelongsToSchedule(
        AuditSchedule $auditSchedule,
        AuditEvaluation $auditEvaluation
    ): void {
        abort_unless(
            (int) $auditEvaluation
                ->audit_schedule_id
                === (int) $auditSchedule->id,
            404
        );
    }
}