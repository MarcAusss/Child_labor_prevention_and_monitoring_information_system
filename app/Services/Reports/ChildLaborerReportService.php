<?php

namespace App\Services\Reports;

use App\Models\ChildLaborer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChildLaborerReportService
{
    /**
     * @return array<string, mixed>
     */
    public function filters(Request $request): array
    {
        $status = trim(
            (string) $request->query('status', '')
        );

        $sex = trim(
            (string) $request->query('sex', '')
        );

        $employment = trim(
            (string) $request->query(
                'employment',
                ''
            )
        );

        $education = trim(
            (string) $request->query(
                'education',
                ''
            )
        );

        $intervention = trim(
            (string) $request->query(
                'intervention',
                ''
            )
        );

        $sort = trim(
            (string) $request->query(
                'sort',
                'created_at'
            )
        );

        $direction = strtolower(
            trim(
                (string) $request->query(
                    'direction',
                    'desc'
                )
            )
        );

        $ageMin = $request->integer(
            'age_min'
        );

        $ageMax = $request->integer(
            'age_max'
        );

        return [
            'search' => trim(
                (string) $request->query(
                    'search',
                    ''
                )
            ),

            'status' => in_array(
                $status,
                $this->statusOptions(),
                true
            )
                ? $status
                : '',

            'sex' => in_array(
                $sex,
                [
                    'Male',
                    'Female',
                ],
                true
            )
                ? $sex
                : '',

            'age_min' => $ageMin >= 0
                && $ageMin <= 99
                    ? $ageMin
                    : null,

            'age_max' => $ageMax >= 0
                && $ageMax <= 99
                    ? $ageMax
                    : null,

            'region_id' => $request->integer(
                'region_id'
            ) ?: null,

            'province_id' => $request->integer(
                'province_id'
            ) ?: null,

            'employment' => in_array(
                $employment,
                [
                    'current',
                    'none',
                ],
                true
            )
                ? $employment
                : '',

            'education' => in_array(
                $education,
                [
                    'current',
                    'none',
                ],
                true
            )
                ? $education
                : '',

            'intervention' => in_array(
                $intervention,
                [
                    'with',
                    'without',
                ],
                true
            )
                ? $intervention
                : '',

            'created_from' => $this->validDate(
                $request->query(
                    'created_from'
                )
            ),

            'created_to' => $this->validDate(
                $request->query(
                    'created_to'
                )
            ),

            'sort' => in_array(
                $sort,
                [
                    'profile_number',
                    'name',
                    'birth_date',
                    'status',
                    'created_at',
                ],
                true
            )
                ? $sort
                : 'created_at',

            'direction' => in_array(
                $direction,
                [
                    'asc',
                    'desc',
                ],
                true
            )
                ? $direction
                : 'desc',
        ];
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return Builder<ChildLaborer>
     */
    public function query(
        array $filters,
        User $user
    ): Builder {
        $query = ChildLaborer::query()
            ->with([
                'assignedOfficer:id,name,email',

                'primaryGuardian',

                'residentialAddress.region',
                'residentialAddress.province',
                'residentialAddress.locality',
                'residentialAddress.barangay',

                'currentEducation',

                'currentEmployment',
            ])
            ->withCount([
                'interventions',
            ]);

        /*
         * Viewer reports are deliberately limited to submitted
         * and approved profiles. Draft, returned, archived, and
         * deleted records are internal administrative records.
         */
        if ($user->isViewer()) {
            $query->whereIn(
                'status',
                [
                    ChildLaborer::STATUS_SUBMITTED,
                    ChildLaborer::STATUS_APPROVED,
                ]
            );
        }

        $query
            ->when(
                $filters['search'] !== '',
                function (
                    Builder $query
                ) use ($filters): void {
                    $search = $filters['search'];

                    $query->where(
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
                                    'middle_name',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhere(
                                    'last_name',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhere(
                                    'contact_number',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhereHas(
                                    'primaryGuardian',
                                    function (
                                        Builder $query
                                    ) use ($search): void {
                                        $query->where(
                                            'full_name',
                                            'like',
                                            '%'.$search.'%'
                                        );
                                    }
                                )
                                ->orWhereHas(
                                    'residentialAddress',
                                    function (
                                        Builder $query
                                    ) use ($search): void {
                                        $query
                                            ->where(
                                                'street',
                                                'like',
                                                '%'.$search.'%'
                                            )
                                            ->orWhereHas(
                                                'barangay',
                                                fn (
                                                    Builder $query
                                                ) => $query->where(
                                                    'name',
                                                    'like',
                                                    '%'.$search.'%'
                                                )
                                            )
                                            ->orWhereHas(
                                                'locality',
                                                fn (
                                                    Builder $query
                                                ) => $query->where(
                                                    'name',
                                                    'like',
                                                    '%'.$search.'%'
                                                )
                                            )
                                            ->orWhereHas(
                                                'province',
                                                fn (
                                                    Builder $query
                                                ) => $query->where(
                                                    'name',
                                                    'like',
                                                    '%'.$search.'%'
                                                )
                                            );
                                    }
                                );
                        }
                    );
                }
            )
            ->when(
                $filters['status'] !== '',
                fn (Builder $query) =>
                    $query->where(
                        'status',
                        $filters['status']
                    )
            )
            ->when(
                $filters['sex'] !== '',
                fn (Builder $query) =>
                    $query->where(
                        'sex',
                        $filters['sex']
                    )
            )
            ->when(
                $filters['age_min'] !== null,
                fn (Builder $query) =>
                    $query->whereDate(
                        'birth_date',
                        '<=',
                        today()
                            ->subYears(
                                (int) $filters[
                                    'age_min'
                                ]
                            )
                            ->format('Y-m-d')
                    )
            )
            ->when(
                $filters['age_max'] !== null,
                fn (Builder $query) =>
                    $query->whereDate(
                        'birth_date',
                        '>',
                        today()
                            ->subYears(
                                ((int) $filters[
                                    'age_max'
                                ]) + 1
                            )
                            ->format('Y-m-d')
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
                                $filters[
                                    'region_id'
                                ]
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
                                $filters[
                                    'province_id'
                                ]
                            )
                    )
            )
            ->when(
                $filters['employment']
                    === 'current',
                fn (Builder $query) =>
                    $query->whereHas(
                        'currentEmployment'
                    )
            )
            ->when(
                $filters['employment']
                    === 'none',
                fn (Builder $query) =>
                    $query->whereDoesntHave(
                        'currentEmployment'
                    )
            )
            ->when(
                $filters['education']
                    === 'current',
                fn (Builder $query) =>
                    $query->whereHas(
                        'currentEducation'
                    )
            )
            ->when(
                $filters['education']
                    === 'none',
                fn (Builder $query) =>
                    $query->whereDoesntHave(
                        'currentEducation'
                    )
            )
            ->when(
                $filters['intervention']
                    === 'with',
                fn (Builder $query) =>
                    $query->whereHas(
                        'interventions'
                    )
            )
            ->when(
                $filters['intervention']
                    === 'without',
                fn (Builder $query) =>
                    $query->whereDoesntHave(
                        'interventions'
                    )
            )
            ->when(
                $filters['created_from'] !== '',
                fn (Builder $query) =>
                    $query->whereDate(
                        'created_at',
                        '>=',
                        $filters[
                            'created_from'
                        ]
                    )
            )
            ->when(
                $filters['created_to'] !== '',
                fn (Builder $query) =>
                    $query->whereDate(
                        'created_at',
                        '<=',
                        $filters[
                            'created_to'
                        ]
                    )
            );

        $this->applySorting(
            $query,
            $filters['sort'],
            $filters['direction']
        );

        return $query;
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

    public function canViewProfileReport(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        if (
            $user->isSuperAdmin()
            || $user->isAdmin()
        ) {
            return true;
        }

        if ($user->isViewer()) {
            return in_array(
                $childLaborer->status,
                [
                    ChildLaborer::STATUS_SUBMITTED,
                    ChildLaborer::STATUS_APPROVED,
                ],
                true
            );
        }

        return false;
    }

    public function includeSensitiveSections(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->isAdmin();
    }

    public function loadProfileReport(
        ChildLaborer $childLaborer,
        User $user
    ): ChildLaborer {
        $relations = [
            'creator:id,name,email',
            'assignedOfficer:id,name,email',
            'reviewer:id,name,email',

            'birthInformation.region',
            'birthInformation.province',
            'birthInformation.locality',
            'birthInformation.barangay',

            'residentialAddress.region',
            'residentialAddress.province',
            'residentialAddress.locality',
            'residentialAddress.barangay',

            'parentGuardians',
            'householdMembers',

            'educationRecords',

            'employmentRecords.workHazards',

            'interventions',

            'documents' => function (
                $query
            ) use ($user): void {
                $query
                    ->visibleTo($user)
                    ->with(
                        'uploader:id,name,email'
                    )
                    ->orderByDesc(
                        'uploaded_at'
                    );
            },
        ];

        if (
            $this->includeSensitiveSections(
                $user
            )
        ) {
            $relations[] =
                'healthInformationRecords';

            $relations[] =
                'auditSchedules.creator:id,name,email';

            $relations[] =
                'auditSchedules.assignedAdministrator:id,name,email';

            $relations[] =
                'auditSchedules.evaluations.evaluator:id,name,email';
        }

        $childLaborer->load(
            $relations
        );

        return $childLaborer;
    }

    /**
     * @return array<string, mixed>
     */
    public function masterRow(
        ChildLaborer $childLaborer
    ): array {
        $address = $childLaborer
            ->residentialAddress;

        $guardian = $childLaborer
            ->primaryGuardian;

        $education = $childLaborer
            ->currentEducation;

        $employment = $childLaborer
            ->currentEmployment;

        return [
            'id' => $childLaborer->id,

            'profile_number' =>
                $childLaborer->profile_number,

            'full_name' =>
                $childLaborer->full_name,

            'sex' =>
                $childLaborer->sex,

            'birth_date' =>
                $childLaborer->birth_date
                    ?->format('Y-m-d'),

            'age' =>
                $childLaborer->birth_date
                    ?->age,

            'status' =>
                $childLaborer->status,

            'region' =>
                $address?->region?->name,

            'province' =>
                $address?->province?->name,

            'locality' =>
                $address?->locality?->name,

            'barangay' =>
                $address?->barangay?->name,

            'address' =>
                $this->addressText($address),

            'guardian_name' =>
                $guardian?->full_name,

            'guardian_contact' =>
                $this->firstValue(
                    $guardian,
                    [
                        'contact_number',
                        'contact_no',
                    ]
                ),

            'education_status' =>
                $education?->enrollment_status,

            'grade_year_level' =>
                $education?->grade_year_level,

            'school_name' =>
                $education?->school_name,

            'currently_working' =>
                $employment
                    ? 'Yes'
                    : 'No',

            'occupation' =>
                $employment?->occupation,

            'employer_name' =>
                $employment?->employer_name,

            'interventions_count' =>
                (int) (
                    $childLaborer
                        ->interventions_count
                    ?? 0
                ),

            'assigned_officer' =>
                $childLaborer
                    ->assignedOfficer?->name,

            'created_at' =>
                $childLaborer->created_at
                    ?->format(
                        'Y-m-d H:i:s'
                    ),
        ];
    }

    public function addressText(
        mixed $address
    ): string {
        if (! $address) {
            return 'Not recorded';
        }

        $parts = [
            $this->firstValue(
                $address,
                [
                    'house_number',
                    'house_no',
                ]
            ),

            $this->firstValue(
                $address,
                [
                    'street',
                    'street_name',
                ]
            ),

            $address->barangay?->name,
            $address->locality?->name,
            $address->province?->name,
            $address->region?->name,

            $this->firstValue(
                $address,
                [
                    'postal_code',
                    'postal',
                ]
            ),
        ];

        $parts = array_values(
            array_filter(
                $parts,
                fn (mixed $value): bool =>
                    filled($value)
            )
        );

        return $parts !== []
            ? implode(', ', $parts)
            : 'Not recorded';
    }

    public function photoDataUri(
        ChildLaborer $childLaborer
    ): ?string {
        $path = trim(
            (string) $childLaborer->photo_path
        );

        if ($path === '') {
            return null;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            return null;
        }

        $contents = $disk->get($path);

        if ($contents === '') {
            return null;
        }

        $mimeType = $disk->mimeType($path)
            ?: 'image/jpeg';

        return 'data:'.$mimeType.';base64,'
            .base64_encode($contents);
    }

    private function firstValue(
        mixed $model,
        array $keys
    ): mixed {
        if (! $model) {
            return null;
        }

        foreach ($keys as $key) {
            $value = data_get(
                $model,
                $key
            );

            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param Builder<ChildLaborer> $query
     */
    private function applySorting(
        Builder $query,
        string $sort,
        string $direction
    ): void {
        if ($sort === 'name') {
            $query
                ->orderBy(
                    'last_name',
                    $direction
                )
                ->orderBy(
                    'first_name',
                    $direction
                );

            return;
        }

        $query->orderBy(
            $sort,
            $direction
        );

        if ($sort !== 'profile_number') {
            $query->orderBy(
                'profile_number'
            );
        }
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