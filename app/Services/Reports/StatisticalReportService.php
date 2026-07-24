<?php

namespace App\Services\Reports;

use App\Models\AuditEvaluation;
use App\Models\AuditSchedule;
use App\Models\ChildLaborer;
use App\Models\EducationRecord;
use App\Models\EmploymentRecord;
use App\Models\Intervention;
use App\Models\User;
use App\Models\WorkHazard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticalReportService
{
    /**
     * @return array<string, mixed>
     */
    public function filters(Request $request): array
    {
        $status = trim((string) $request->query('status', ''));

        return [
            'status' => in_array(
                $status,
                $this->statusOptions(),
                true
            ) ? $status : '',

            'region_id' => $request->integer('region_id') ?: null,

            'province_id' => $request->integer('province_id') ?: null,

            'from' => $this->validDate(
                $request->query('from')
            ),

            'to' => $this->validDate(
                $request->query('to')
            ),
        ];
    }

    /**
     * @return array<string>
     */
    public function statusOptions(): array
    {
        return [
            ChildLaborer::STATUS_DRAFT,
            ChildLaborer::STATUS_SUBMITTED,
            ChildLaborer::STATUS_RETURNED,
            ChildLaborer::STATUS_APPROVED,
            ChildLaborer::STATUS_ARCHIVED,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    public function build(array $filters, User $user): array
    {
        $profileIds = $this->baseQuery(
            $filters,
            $user
        )->pluck('child_laborers.id');

        return [
            'summary' => $this->summary($profileIds),

            'sexDistribution' =>
                $this->sexDistribution($profileIds),

            'ageDistribution' =>
                $this->ageDistribution($profileIds),

            'statusDistribution' =>
                $this->statusDistribution($profileIds),

            'profileTrend' =>
                $this->profileTrend($profileIds),

            'regions' =>
                $this->regionDistribution($profileIds),

            'provinces' =>
                $this->provinceDistribution($profileIds),

            'education' =>
                $this->educationDistribution($profileIds),

            'employment' =>
                $this->employmentDistribution($profileIds),

            'workTypes' =>
                $this->workTypeDistribution($profileIds),

            'hazards' =>
                $this->hazardDistribution($profileIds),

            'interventionTypes' =>
                $this->interventionTypeDistribution($profileIds),

            'interventionStatuses' =>
                $this->interventionStatusDistribution($profileIds),

            'auditScheduleStatuses' =>
                $this->auditScheduleDistribution($profileIds),

            'auditEvaluationStatuses' =>
                $this->auditEvaluationDistribution($profileIds),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return Builder<ChildLaborer>
     */
    public function baseQuery(
        array $filters,
        User $user
    ): Builder {
        $query = ChildLaborer::query();

        if ($user->isViewer()) {
            $query->whereIn(
                'status',
                [
                    ChildLaborer::STATUS_SUBMITTED,
                    ChildLaborer::STATUS_APPROVED,
                ]
            );
        }

        return $query
            ->when(
                $filters['status'] !== '',
                fn (Builder $query) =>
                    $query->where(
                        'status',
                        $filters['status']
                    )
            )
            ->when(
                $filters['region_id'],
                fn (Builder $query) =>
                    $query->whereHas(
                        'residentialAddress',
                        fn (Builder $query) =>
                            $query->where(
                                'region_id',
                                $filters['region_id']
                            )
                    )
            )
            ->when(
                $filters['province_id'],
                fn (Builder $query) =>
                    $query->whereHas(
                        'residentialAddress',
                        fn (Builder $query) =>
                            $query->where(
                                'province_id',
                                $filters['province_id']
                            )
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

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return array<string, int|float>
     */
    private function summary(Collection $profileIds): array
    {
        $total = $profileIds->count();

        if ($total === 0) {
            return [
                'total_profiles' => 0,
                'currently_working' => 0,
                'with_hazards' => 0,
                'with_interventions' => 0,
                'completed_audits' => 0,
                'intervention_value' => 0.0,
            ];
        }

        $currentlyWorking = EmploymentRecord::query()
            ->whereIn('child_laborer_id', $profileIds)
            ->where('is_current', true)
            ->distinct()
            ->count('child_laborer_id');

        $withHazards = WorkHazard::query()
            ->join(
                'employment_records',
                'employment_records.id',
                '=',
                'work_hazards.employment_record_id'
            )
            ->whereIn(
                'employment_records.child_laborer_id',
                $profileIds
            )
            ->distinct()
            ->count(
                'employment_records.child_laborer_id'
            );

        $withInterventions = Intervention::query()
            ->whereIn('child_laborer_id', $profileIds)
            ->distinct()
            ->count('child_laborer_id');

        $completedAudits = AuditSchedule::query()
            ->whereIn('child_laborer_id', $profileIds)
            ->where(
                'status',
                AuditSchedule::STATUS_COMPLETED
            )
            ->distinct()
            ->count('child_laborer_id');

        $interventionValue = (float) Intervention::query()
            ->whereIn('child_laborer_id', $profileIds)
            ->sum('amount');

        return [
            'total_profiles' => $total,
            'currently_working' => $currentlyWorking,
            'with_hazards' => $withHazards,
            'with_interventions' => $withInterventions,
            'completed_audits' => $completedAudits,
            'intervention_value' => $interventionValue,
        ];
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function sexDistribution(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = ChildLaborer::query()
            ->whereIn('id', $profileIds)
            ->select(
                'sex',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('sex')
            ->orderByDesc('total')
            ->get();

        return $this->withPercentages(
            $rows->map(
                fn ($row): array => [
                    'label' => $row->sex ?: 'Not recorded',
                    'total' => (int) $row->total,
                ]
            )
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function ageDistribution(
        Collection $profileIds
    ): Collection {
        $groups = [
            '0–5 years' => 0,
            '6–9 years' => 0,
            '10–12 years' => 0,
            '13–15 years' => 0,
            '16–17 years' => 0,
            '18 years and above' => 0,
            'Not recorded' => 0,
        ];

        if ($profileIds->isEmpty()) {
            return $this->withPercentages(
                collect($groups)->map(
                    fn (int $total, string $label): array => [
                        'label' => $label,
                        'total' => $total,
                    ]
                )->values()
            );
        }

        ChildLaborer::query()
            ->whereIn('id', $profileIds)
            ->get(['birth_date'])
            ->each(function (ChildLaborer $profile) use (&$groups): void {
                if (! $profile->birth_date) {
                    $groups['Not recorded']++;

                    return;
                }

                $age = $profile->birth_date->age;

                $label = match (true) {
                    $age <= 5 => '0–5 years',
                    $age <= 9 => '6–9 years',
                    $age <= 12 => '10–12 years',
                    $age <= 15 => '13–15 years',
                    $age <= 17 => '16–17 years',
                    default => '18 years and above',
                };

                $groups[$label]++;
            });

        return $this->withPercentages(
            collect($groups)->map(
                fn (int $total, string $label): array => [
                    'label' => $label,
                    'total' => $total,
                ]
            )->values()
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function statusDistribution(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = ChildLaborer::query()
            ->whereIn('id', $profileIds)
            ->select(
                'status',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        return $this->withPercentages(
            $rows->map(
                fn ($row): array => [
                    'label' => $row->status ?: 'Not recorded',
                    'total' => (int) $row->total,
                ]
            )
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function profileTrend(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = ChildLaborer::query()
            ->whereIn('id', $profileIds)
            ->selectRaw(
                "DATE_FORMAT(created_at, '%Y-%m') AS month_key"
            )
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get();

        return $this->withPercentages(
            $rows->map(
                fn ($row): array => [
                    'label' => Carbon::createFromFormat(
                        'Y-m',
                        $row->month_key
                    )->format('M Y'),

                    'total' => (int) $row->total,
                ]
            )
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function regionDistribution(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = DB::table('residential_addresses')
            ->leftJoin(
                'regions',
                'regions.id',
                '=',
                'residential_addresses.region_id'
            )
            ->whereIn(
                'residential_addresses.child_laborer_id',
                $profileIds
            )
            ->selectRaw(
                "COALESCE(regions.name, 'Not recorded') AS label"
            )
            ->selectRaw(
                'COUNT(DISTINCT residential_addresses.child_laborer_id) AS total'
            )
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        return $this->withPercentages(
            $rows->map(
                fn ($row): array => [
                    'label' => (string) $row->label,
                    'total' => (int) $row->total,
                ]
            )
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function provinceDistribution(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = DB::table('residential_addresses')
            ->leftJoin(
                'provinces',
                'provinces.id',
                '=',
                'residential_addresses.province_id'
            )
            ->whereIn(
                'residential_addresses.child_laborer_id',
                $profileIds
            )
            ->selectRaw(
                "COALESCE(provinces.name, 'Regional / Not recorded') AS label"
            )
            ->selectRaw(
                'COUNT(DISTINCT residential_addresses.child_laborer_id) AS total'
            )
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        return $this->withPercentages(
            $rows->map(
                fn ($row): array => [
                    'label' => (string) $row->label,
                    'total' => (int) $row->total,
                ]
            )
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function educationDistribution(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = EducationRecord::query()
            ->whereIn('child_laborer_id', $profileIds)
            ->where('is_current', true)
            ->select(
                'enrollment_status',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('enrollment_status')
            ->orderByDesc('total')
            ->get()
            ->map(
                fn ($row): array => [
                    'label' =>
                        $row->enrollment_status
                        ?: 'Not recorded',

                    'total' => (int) $row->total,
                ]
            );

        $withoutCurrentRecord = ChildLaborer::query()
            ->whereIn('id', $profileIds)
            ->whereDoesntHave('currentEducation')
            ->count();

        if ($withoutCurrentRecord > 0) {
            $rows->push([
                'label' => 'No current education record',
                'total' => $withoutCurrentRecord,
            ]);
        }

        return $this->withPercentages(
            $rows->sortByDesc('total')->values()
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function employmentDistribution(
        Collection $profileIds
    ): Collection {
        $total = $profileIds->count();

        if ($total === 0) {
            return collect();
        }

        $working = EmploymentRecord::query()
            ->whereIn('child_laborer_id', $profileIds)
            ->where('is_current', true)
            ->distinct()
            ->count('child_laborer_id');

        return $this->withPercentages(
            collect([
                [
                    'label' => 'Currently working',
                    'total' => $working,
                ],
                [
                    'label' => 'No current employment',
                    'total' => max(0, $total - $working),
                ],
            ])
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function workTypeDistribution(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = EmploymentRecord::query()
            ->whereIn('child_laborer_id', $profileIds)
            ->where('is_current', true)
            ->select(
                'work_type',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('work_type')
            ->orderByDesc('total')
            ->get();

        return $this->withPercentages(
            $rows->map(
                fn ($row): array => [
                    'label' => $row->work_type ?: 'Not recorded',
                    'total' => (int) $row->total,
                ]
            )
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function hazardDistribution(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = WorkHazard::query()
            ->join(
                'employment_records',
                'employment_records.id',
                '=',
                'work_hazards.employment_record_id'
            )
            ->whereIn(
                'employment_records.child_laborer_id',
                $profileIds
            )
            ->select(
                'work_hazards.hazard_type',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('work_hazards.hazard_type')
            ->orderByDesc('total')
            ->get();

        return $this->withPercentages(
            $rows->map(
                fn ($row): array => [
                    'label' =>
                        $row->hazard_type
                        ?: 'Not recorded',

                    'total' => (int) $row->total,
                ]
            )
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function interventionTypeDistribution(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = Intervention::query()
            ->whereIn('child_laborer_id', $profileIds)
            ->select(
                'intervention_type',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('intervention_type')
            ->orderByDesc('total')
            ->get();

        return $this->withPercentages(
            $rows->map(
                fn ($row): array => [
                    'label' =>
                        $row->intervention_type
                        ?: 'Not recorded',

                    'total' => (int) $row->total,
                ]
            )
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function interventionStatusDistribution(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = Intervention::query()
            ->whereIn('child_laborer_id', $profileIds)
            ->select(
                'status',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        return $this->withPercentages(
            $rows->map(
                fn ($row): array => [
                    'label' => $row->status ?: 'Not recorded',
                    'total' => (int) $row->total,
                ]
            )
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function auditScheduleDistribution(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = AuditSchedule::query()
            ->whereIn('child_laborer_id', $profileIds)
            ->select(
                'status',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        return $this->withPercentages(
            $rows->map(
                fn ($row): array => [
                    'label' => $row->status ?: 'Not recorded',
                    'total' => (int) $row->total,
                ]
            )
        );
    }

    /**
     * @param Collection<int, int> $profileIds
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function auditEvaluationDistribution(
        Collection $profileIds
    ): Collection {
        if ($profileIds->isEmpty()) {
            return collect();
        }

        $rows = AuditEvaluation::query()
            ->join(
                'audit_schedules',
                'audit_schedules.id',
                '=',
                'audit_evaluations.audit_schedule_id'
            )
            ->whereIn(
                'audit_schedules.child_laborer_id',
                $profileIds
            )
            ->select(
                'audit_evaluations.status',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('audit_evaluations.status')
            ->orderByDesc('total')
            ->get();

        return $this->withPercentages(
            $rows->map(
                fn ($row): array => [
                    'label' => $row->status ?: 'Not recorded',
                    'total' => (int) $row->total,
                ]
            )
        );
    }

    /**
     * @param Collection<int, array{label:string,total:int}> $rows
     *
     * @return Collection<int, array{label:string,total:int,percentage:float}>
     */
    private function withPercentages(
        Collection $rows
    ): Collection {
        $total = (int) $rows->sum('total');

        return $rows->map(
            fn (array $row): array => [
                ...$row,

                'percentage' => $total > 0
                    ? round(
                        ($row['total'] / $total) * 100,
                        2
                    )
                    : 0.0,
            ]
        )->values();
    }

    private function validDate(mixed $value): string
    {
        $value = trim((string) $value);

        return preg_match(
            '/^\d{4}-\d{2}-\d{2}$/',
            $value
        ) === 1 ? $value : '';
    }
}
