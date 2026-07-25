<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\AuditEvaluation;
use App\Models\AuditSchedule;
use App\Models\Barangay;
use App\Models\BirthInformation;
use App\Models\ChildLaborer;
use App\Models\ChildLaborerDocument;
use App\Models\EducationRecord;
use App\Models\EmploymentRecord;
use App\Models\HealthInformation;
use App\Models\HouseholdMember;
use App\Models\Intervention;
use App\Models\Locality;
use App\Models\ParentGuardian;
use App\Models\Province;
use App\Models\Region;
use App\Models\ResidentialAddress;
use App\Models\User;
use App\Models\WorkHazard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CLPMISDemoSeeder extends Seeder
{
    private const SEEDED_BY =
        'CLPMISDemoSeeder';

    /**
     * @var array<string, User>
     */
    private array $users = [];

    /**
     * @var array<string, int|null>
     */
    private array $location = [];

    /**
     * @var array<string, ChildLaborer>
     */
    private array $profiles = [];

    public function run(): void
    {
        if (! app()->environment([
            'local',
            'testing',
        ])) {
            $this->command?->warn(
                'CLPMIS demo data was skipped outside local/testing.'
            );

            return;
        }

        $this->call([
            RoleSeeder::class,
            DefaultUserSeeder::class,
        ]);

        Model::withoutEvents(function (): void {
            DB::transaction(function (): void {
                $this->loadUsers();
                $this->location =
                    $this->resolveLocation();

                $this->removePreviousSeededSystemRows();
                $this->seedProfiles();
                $this->seedAudits();
                $this->seedActivityLogs();
                $this->seedNotifications();
            });
        });

        $this->command?->newLine();
        $this->command?->info(
            'CLPMIS demonstration data seeded successfully.'
        );

        $this->command?->table(
            [
                'Role',
                'Email',
                'Password',
            ],
            [
                [
                    'Super Admin',
                    'superadmin@clpmis.test',
                    DefaultUserSeeder::DEFAULT_PASSWORD,
                ],
                [
                    'Admin',
                    'admin@clpmis.test',
                    DefaultUserSeeder::DEFAULT_PASSWORD,
                ],
                [
                    'Admin / Audit',
                    'admin.audit@clpmis.test',
                    DefaultUserSeeder::DEFAULT_PASSWORD,
                ],
                [
                    'Profiling Officer',
                    'profiling@clpmis.test',
                    DefaultUserSeeder::DEFAULT_PASSWORD,
                ],
                [
                    'Profiling Officer 2',
                    'profiling2@clpmis.test',
                    DefaultUserSeeder::DEFAULT_PASSWORD,
                ],
                [
                    'Viewer',
                    'viewer@clpmis.test',
                    DefaultUserSeeder::DEFAULT_PASSWORD,
                ],
            ]
        );
    }

    private function loadUsers(): void
    {
        $emails = [
            'superadmin@clpmis.test',
            'admin@clpmis.test',
            'admin.audit@clpmis.test',
            'profiling@clpmis.test',
            'profiling2@clpmis.test',
            'viewer@clpmis.test',
        ];

        $users = User::query()
            ->whereIn('email', $emails)
            ->get()
            ->keyBy('email');

        foreach ($emails as $email) {
            $user = $users->get($email);

            if (! $user) {
                throw new RuntimeException(
                    'Missing seeded user: '.$email
                );
            }

            $this->users[$email] = $user;
        }
    }

    /**
     * @return array<string, int|null>
     */
    private function resolveLocation(): array
    {
        $barangay = Barangay::query()
            ->with([
                'region',
                'province',
                'locality',
            ])
            ->where('is_active', true)
            ->whereHas('region')
            ->whereHas('locality')
            ->orderBy('id')
            ->first();

        if (! $barangay) {
            $region = Region::query()->updateOrCreate(
                [
                    'psgc_code' => '9900000001',
                ],
                [
                    'name' => 'Region V (Demo)',
                    'is_active' => true,
                ]
            );

            $province = Province::query()->updateOrCreate(
                [
                    'psgc_code' => '9900000002',
                ],
                [
                    'region_id' => $region->id,
                    'name' => 'Albay (Demo)',
                    'is_active' => true,
                ]
            );

            $locality = Locality::query()->updateOrCreate(
                [
                    'psgc_code' => '9900000003',
                ],
                [
                    'region_id' => $region->id,
                    'province_id' => $province->id,
                    'name' => 'Legazpi City (Demo)',
                    'geographic_level' =>
                        Locality::LEVEL_CITY,
                    'is_active' => true,
                ]
            );

            $barangay = Barangay::query()->updateOrCreate(
                [
                    'psgc_code' => '9900000004',
                ],
                [
                    'region_id' => $region->id,
                    'province_id' => $province->id,
                    'locality_id' => $locality->id,
                    'name' => 'Rawis (Demo)',
                    'urban_rural' => 'U',
                    'status' => 'Active',
                    'is_active' => true,
                ]
            );

            $barangay->load([
                'region',
                'province',
                'locality',
            ]);
        }

        return [
            'region_id' => $barangay->region_id,
            'province_id' => $barangay->province_id,
            'locality_id' => $barangay->locality_id,
            'barangay_id' => $barangay->id,
        ];
    }

    private function seedProfiles(): void
    {
        $records = $this->profileRecords();

        foreach ($records as $index => $record) {
            $officer = $this->users[
                $record['officer_email']
            ];

            $reviewer = $this->users[
                $record['reviewer_email']
            ];

            $identity = [
                'first_name' => $record['first_name'],
                'middle_name' => $record['middle_name'],
                'last_name' => $record['last_name'],
                'suffix' => $record['suffix'],
                'birth_date' => $record['birth_date'],
            ];

            $profile = ChildLaborer::query()
                ->updateOrCreate(
                    [
                        'profile_number' =>
                            $record['profile_number'],
                    ],
                    [
                        'created_by' => $officer->id,
                        'assigned_to' => $officer->id,
                        'reviewed_by' =>
                            $this->requiresReviewer(
                                $record['status']
                            )
                                ? $reviewer->id
                                : null,
                        ...$identity,
                        'sex' => $record['sex'],
                        'civil_status' => 'Single',
                        'nationality' => 'Filipino',
                        'religion' => $record['religion'],
                        'contact_number' =>
                            $record['contact_number'],
                        'duplicate_key' =>
                            ChildLaborer::makeDuplicateKey(
                                $identity
                            ),
                        ...$this->workflowValues(
                            $record['status'],
                            $record['created_months_ago']
                        ),
                    ]
                );

            $createdAt = now()
                ->subMonths(
                    $record['created_months_ago']
                )
                ->subDays($index + 1);

            $profile->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt
                    ->copy()
                    ->addDays(
                        min(
                            12,
                            $index + 2
                        )
                    ),
            ])->saveQuietly();

            $this->profiles[
                $record['profile_number']
            ] = $profile;

            $this->seedLocationData(
                $profile,
                $record,
                $index
            );

            $this->seedFamilyData(
                $profile,
                $record,
                $index
            );

            $this->seedEducation(
                $profile,
                $record
            );

            if ($record['employment']) {
                $this->seedEmployment(
                    $profile,
                    $record['employment']
                );
            }

            $this->seedHealth(
                $profile,
                $record['health']
            );

            foreach (
                $record['interventions']
                as $intervention
            ) {
                $this->seedIntervention(
                    $profile,
                    $officer,
                    $intervention
                );
            }

            if ($record['has_document']) {
                $this->seedDocument(
                    $profile,
                    $officer
                );
            }
        }
    }

    private function seedLocationData(
        ChildLaborer $profile,
        array $record,
        int $index
    ): void {
        BirthInformation::query()->updateOrCreate(
            [
                'child_laborer_id' => $profile->id,
            ],
            [
                ...$this->location,
                'place_of_birth' =>
                    'Legazpi City, Albay',
            ]
        );

        ResidentialAddress::query()->updateOrCreate(
            [
                'child_laborer_id' => $profile->id,
            ],
            [
                ...$this->location,
                'house_number' =>
                    (string) (100 + $index),
                'street' =>
                    $record['street'],
                'sitio_purok' =>
                    'Purok '.(($index % 7) + 1),
                'postal_code' => '4500',
                'landmark' =>
                    'Near the barangay hall',
            ]
        );
    }

    private function seedFamilyData(
        ChildLaborer $profile,
        array $record,
        int $index
    ): void {
        ParentGuardian::query()->updateOrCreate(
            [
                'child_laborer_id' => $profile->id,
                'full_name' => $record['guardian_name'],
            ],
            [
                'relationship' =>
                    $record['guardian_relationship'],
                'contact_number' =>
                    '0917'.str_pad(
                        (string) (7000000 + $index),
                        7,
                        '0',
                        STR_PAD_LEFT
                    ),
                'occupation' =>
                    $record['guardian_occupation'],
                'educational_attainment' =>
                    'High School Graduate',
                'monthly_income' =>
                    $record['guardian_income'],
                'is_primary' => true,
            ]
        );

        $household = [
            'full_name' =>
                $record['sibling_name'],
            'relationship' => 'Sibling',
            'sex' => $record['sex'] === 'Male'
                ? 'Female'
                : 'Male',
            'birth_date' => Carbon::parse(
                $record['birth_date']
            )->subYears(3)->format('Y-m-d'),
            'civil_status' => 'Single',
            'educational_attainment' =>
                'Elementary Level',
            'occupation' => null,
            'monthly_income' => null,
        ];

        $household['duplicate_key'] =
            HouseholdMember::makeDuplicateKey(
                $household
            );

        HouseholdMember::query()->updateOrCreate(
            [
                'child_laborer_id' => $profile->id,
                'duplicate_key' =>
                    $household['duplicate_key'],
            ],
            $household
        );
    }

    private function seedEducation(
        ChildLaborer $profile,
        array $record
    ): void {
        $education = $record['education'];

        $education['duplicate_key'] =
            EducationRecord::makeDuplicateKey(
                $education
            );

        EducationRecord::query()->updateOrCreate(
            [
                'child_laborer_id' => $profile->id,
                'duplicate_key' =>
                    $education['duplicate_key'],
            ],
            [
                ...$education,
                'child_laborer_id' => $profile->id,
            ]
        );
    }

    private function seedEmployment(
        ChildLaborer $profile,
        array $employment
    ): void {
        $hazards = $employment['hazards'];
        unset($employment['hazards']);

        $employment['duplicate_key'] =
            EmploymentRecord::makeDuplicateKey(
                $employment
            );

        $employmentRecord =
            EmploymentRecord::query()
                ->updateOrCreate(
                    [
                        'child_laborer_id' =>
                            $profile->id,
                        'duplicate_key' =>
                            $employment[
                                'duplicate_key'
                            ],
                    ],
                    [
                        ...$employment,
                        'child_laborer_id' =>
                            $profile->id,
                    ]
                );

        foreach ($hazards as $hazard) {
            $hazard['duplicate_key'] =
                WorkHazard::makeDuplicateKey(
                    $hazard
                );

            WorkHazard::query()->updateOrCreate(
                [
                    'employment_record_id' =>
                        $employmentRecord->id,
                    'duplicate_key' =>
                        $hazard['duplicate_key'],
                ],
                [
                    ...$hazard,
                    'employment_record_id' =>
                        $employmentRecord->id,
                ]
            );
        }
    }

    private function seedHealth(
        ChildLaborer $profile,
        array $health
    ): void {
        $health['duplicate_key'] =
            HealthInformation::makeDuplicateKey(
                $health
            );

        HealthInformation::query()->updateOrCreate(
            [
                'child_laborer_id' => $profile->id,
                'duplicate_key' =>
                    $health['duplicate_key'],
            ],
            [
                ...$health,
                'child_laborer_id' => $profile->id,
            ]
        );
    }

    private function seedIntervention(
        ChildLaborer $profile,
        User $officer,
        array $intervention
    ): void {
        $intervention['duplicate_key'] =
            Intervention::makeDuplicateKey(
                $intervention
            );

        Intervention::query()->updateOrCreate(
            [
                'child_laborer_id' => $profile->id,
                'duplicate_key' =>
                    $intervention['duplicate_key'],
            ],
            [
                ...$intervention,
                'child_laborer_id' => $profile->id,
                'created_by' => $officer->id,
                'updated_by' =>
                    $this->users[
                        'admin@clpmis.test'
                    ]->id,
            ]
        );
    }

    private function seedDocument(
        ChildLaborer $profile,
        User $officer
    ): void {
        $content = implode(PHP_EOL, [
            'CLPMIS DEMONSTRATION DOCUMENT',
            'Profile: '.$profile->profile_number,
            'Name: '.$profile->full_name,
            'This is synthetic seed data for local testing only.',
        ]);

        $storedName =
            Str::slug(
                $profile->profile_number
            )
            .'-case-summary.txt';

        $filePath =
            'demo/'
            .$profile->profile_number
            .'/'.$storedName;

        Storage::disk(
            'clpmis_documents'
        )->put(
            $filePath,
            $content
        );

        ChildLaborerDocument::query()
            ->updateOrCreate(
                [
                    'child_laborer_id' =>
                        $profile->id,
                    'file_path' => $filePath,
                ],
                [
                    'uploaded_by' => $officer->id,
                    'document_type' =>
                        ChildLaborerDocument::TYPE_ASSESSMENT,
                    'original_name' =>
                        'Demo Case Summary.txt',
                    'stored_name' => $storedName,
                    'mime_type' => 'text/plain',
                    'extension' => 'txt',
                    'file_size' => strlen($content),
                    'checksum_sha256' =>
                        hash('sha256', $content),
                    'description' =>
                        'Synthetic demonstration document for UI and access testing.',
                    'is_confidential' => true,
                    'download_count' => 0,
                    'uploaded_at' => now()->subDays(5),
                ]
            );
    }

    private function seedAudits(): void
    {
        $admin = $this->users[
            'admin@clpmis.test'
        ];

        $auditAdmin = $this->users[
            'admin.audit@clpmis.test'
        ];

        $audits = [
            [
                'profile' => 'CLPMIS-2026-0001',
                'status' =>
                    AuditSchedule::STATUS_COMPLETED,
                'scheduled_at' =>
                    now()->subDays(20)->setTime(9, 0),
                'assigned_to' => $auditAdmin,
                'remarks' =>
                    'Seeded completed monitoring audit.',
                'evaluation_status' =>
                    AuditEvaluation::STATUS_FINALIZED,
                'findings' =>
                    'The child is no longer engaged in regular street vending and is attending school consistently.',
                'recommendations' =>
                    'Continue education monitoring and household livelihood support for six months.',
            ],
            [
                'profile' => 'CLPMIS-2026-0005',
                'status' =>
                    AuditSchedule::STATUS_IN_PROGRESS,
                'scheduled_at' =>
                    now()->subHours(2),
                'assigned_to' => $admin,
                'remarks' =>
                    'Seeded active case validation audit.',
                'evaluation_status' =>
                    AuditEvaluation::STATUS_DRAFT,
                'findings' =>
                    'Initial interview completed. Supporting school attendance records are being validated.',
                'recommendations' =>
                    'Coordinate with the school focal person and primary guardian.',
            ],
            [
                'profile' => 'CLPMIS-2026-0007',
                'status' =>
                    AuditSchedule::STATUS_SCHEDULED,
                'scheduled_at' =>
                    now()->addDays(5)->setTime(10, 30),
                'assigned_to' => $auditAdmin,
                'remarks' =>
                    'Seeded upcoming household visit.',
                'evaluation_status' => null,
                'findings' => null,
                'recommendations' => null,
            ],
            [
                'profile' => 'CLPMIS-2026-0010',
                'status' =>
                    AuditSchedule::STATUS_CANCELLED,
                'scheduled_at' =>
                    now()->subDays(3)->setTime(13, 0),
                'assigned_to' => $admin,
                'remarks' =>
                    'Seeded cancelled audit due to family relocation.',
                'evaluation_status' => null,
                'findings' => null,
                'recommendations' => null,
            ],
        ];

        foreach ($audits as $record) {
            $profile = $this->profiles[
                $record['profile']
            ];

            $schedule = AuditSchedule::query()
                ->updateOrCreate(
                    [
                        'child_laborer_id' =>
                            $profile->id,
                        'remarks' =>
                            $record['remarks'],
                    ],
                    [
                        'created_by' => $admin->id,
                        'assigned_to' =>
                            $record['assigned_to']->id,
                        'scheduled_at' =>
                            $record['scheduled_at'],
                        'location' =>
                            'Barangay Hall and Household Address',
                        'status' => $record['status'],
                        'started_at' =>
                            in_array(
                                $record['status'],
                                [
                                    AuditSchedule::STATUS_IN_PROGRESS,
                                    AuditSchedule::STATUS_COMPLETED,
                                ],
                                true
                            )
                                ? $record['scheduled_at']
                                : null,
                        'completed_at' =>
                            $record['status']
                                === AuditSchedule::STATUS_COMPLETED
                                ? Carbon::parse(
                                    $record['scheduled_at']
                                )->addHours(2)
                                : null,
                        'cancelled_at' =>
                            $record['status']
                                === AuditSchedule::STATUS_CANCELLED
                                ? Carbon::parse(
                                    $record['scheduled_at']
                                )->subDay()
                                : null,
                    ]
                );

            if (! $record['evaluation_status']) {
                continue;
            }

            AuditEvaluation::query()->updateOrCreate(
                [
                    'audit_schedule_id' => $schedule->id,
                    'evaluated_by' =>
                        $record['assigned_to']->id,
                ],
                [
                    'updated_by' => $admin->id,
                    'evaluation_date' =>
                        Carbon::parse(
                            $record['scheduled_at']
                        )->toDateString(),
                    'findings' =>
                        $record['findings'],
                    'recommendations' =>
                        $record['recommendations'],
                    'status' =>
                        $record['evaluation_status'],
                    'submitted_at' =>
                        $record['evaluation_status']
                            !== AuditEvaluation::STATUS_DRAFT
                            ? now()->subDays(18)
                            : null,
                    'finalized_at' =>
                        $record['evaluation_status']
                            === AuditEvaluation::STATUS_FINALIZED
                            ? now()->subDays(18)
                            : null,
                ]
            );
        }
    }

    private function seedActivityLogs(): void
    {
        $admin = $this->users[
            'admin@clpmis.test'
        ];

        $officer = $this->users[
            'profiling@clpmis.test'
        ];

        $records = [
            [
                'user' => $officer,
                'profile' => 'CLPMIS-2026-0002',
                'action' => ActivityLog::ACTION_SUBMITTED,
                'description' =>
                    'Submitted a child laborer profile for administrative review.',
                'created_at' => now()->subHours(8),
            ],
            [
                'user' => $admin,
                'profile' => 'CLPMIS-2026-0001',
                'action' => ActivityLog::ACTION_APPROVED,
                'description' =>
                    'Approved a complete child laborer profile.',
                'created_at' => now()->subDays(2),
            ],
            [
                'user' => $admin,
                'profile' => 'CLPMIS-2026-0004',
                'action' => ActivityLog::ACTION_RETURNED,
                'description' =>
                    'Returned a profile for correction of employment details.',
                'created_at' => now()->subDays(1),
            ],
            [
                'user' => $officer,
                'profile' => 'CLPMIS-2026-0005',
                'action' => ActivityLog::ACTION_UPDATED,
                'description' =>
                    'Updated intervention and education monitoring information.',
                'created_at' => now()->subHours(4),
            ],
        ];

        foreach ($records as $record) {
            $profile = $this->profiles[
                $record['profile']
            ];

            ActivityLog::query()->create([
                'user_id' => $record['user']->id,
                'actor_name' =>
                    $record['user']->name,
                'role_name' =>
                    $record['user']->role?->name,
                'child_laborer_id' => $profile->id,
                'action' => $record['action'],
                'entity_type' =>
                    ChildLaborer::class,
                'entity_id' => $profile->id,
                'description' =>
                    $record['description'],
                'metadata' => [
                    'seeded_by' => self::SEEDED_BY,
                    'profile_number' =>
                        $profile->profile_number,
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' =>
                    'CLPMIS demonstration seeder',
                'request_method' => 'SEED',
                'route_name' =>
                    'child-laborers.show',
                'url' =>
                    '/child-laborers/'
                    .$profile->id,
                'created_at' =>
                    $record['created_at'],
            ]);
        }
    }

    private function seedNotifications(): void
    {
        $notifications = [
            [
                'user' => 'admin@clpmis.test',
                'key' => 'submitted-profile',
                'title' => 'Profiles awaiting review',
                'message' =>
                    'Two demonstration profiles are ready for administrative review.',
                'type' => 'profile',
                'severity' => 'info',
                'route' =>
                    'child-laborers.index',
                'parameters' => [],
                'read_at' => null,
            ],
            [
                'user' => 'admin.audit@clpmis.test',
                'key' => 'upcoming-audit',
                'title' => 'Upcoming household audit',
                'message' =>
                    'A demonstration household visit is scheduled within five days.',
                'type' => 'audit',
                'severity' => 'warning',
                'route' =>
                    'audit-schedules.index',
                'parameters' => [],
                'read_at' => null,
            ],
            [
                'user' => 'profiling@clpmis.test',
                'key' => 'returned-profile',
                'title' => 'Profile returned for correction',
                'message' =>
                    'CLPMIS-2026-0004 requires corrected employment information.',
                'type' => 'profile',
                'severity' => 'warning',
                'route' =>
                    'child-laborers.show',
                'parameters' => [
                    'childLaborer' =>
                        $this->profiles[
                            'CLPMIS-2026-0004'
                        ]->id,
                ],
                'read_at' => null,
            ],
            [
                'user' => 'profiling2@clpmis.test',
                'key' => 'approved-profile',
                'title' => 'Profile approved',
                'message' =>
                    'CLPMIS-2026-0007 was approved and is ready for monitoring.',
                'type' => 'profile',
                'severity' => 'success',
                'route' =>
                    'child-laborers.show',
                'parameters' => [
                    'childLaborer' =>
                        $this->profiles[
                            'CLPMIS-2026-0007'
                        ]->id,
                ],
                'read_at' => now()->subDay(),
            ],
            [
                'user' => 'viewer@clpmis.test',
                'key' => 'reports-ready',
                'title' => 'Monitoring reports available',
                'message' =>
                    'Updated demonstration reports and statistics are available for viewing.',
                'type' => 'system',
                'severity' => 'info',
                'route' =>
                    'reports.statistics.index',
                'parameters' => [],
                'read_at' => null,
            ],
            [
                'user' => 'superadmin@clpmis.test',
                'key' => 'system-ready',
                'title' => 'Demonstration environment ready',
                'message' =>
                    'Role accounts and complete synthetic case records were seeded successfully.',
                'type' => 'system',
                'severity' => 'success',
                'route' =>
                    'workspace.dashboard',
                'parameters' => [],
                'read_at' => null,
            ],
        ];

        foreach ($notifications as $index => $record) {
            $user = $this->users[
                $record['user']
            ];

            $id = $this->notificationUuid(
                $record['user'].'|'.$record['key']
            );

            DB::table('notifications')->updateOrInsert(
                [
                    'id' => $id,
                ],
                [
                    'type' =>
                        'App\\Notifications\\SystemNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => $record['title'],
                        'message' => $record['message'],
                        'notification_type' =>
                            $record['type'],
                        'severity' =>
                            $record['severity'],
                        'route_name' =>
                            $record['route'],
                        'route_parameters' =>
                            $record['parameters'],
                        'child_laborer_id' =>
                            $record['parameters'][
                                'childLaborer'
                            ] ?? null,
                        'actor_name' =>
                            'CLPMIS Demo Seeder',
                        'metadata' => [
                            'seeded_by' =>
                                self::SEEDED_BY,
                        ],
                    ], JSON_UNESCAPED_SLASHES),
                    'read_at' => $record['read_at'],
                    'created_at' =>
                        now()->subHours($index + 1),
                    'updated_at' =>
                        now()->subHours($index + 1),
                ]
            );
        }
    }

    private function removePreviousSeededSystemRows(): void
    {
        ActivityLog::query()
            ->where(
                'metadata->seeded_by',
                self::SEEDED_BY
            )
            ->delete();

        DB::table('notifications')
            ->where(
                'data->metadata->seeded_by',
                self::SEEDED_BY
            )
            ->delete();
    }

    private function requiresReviewer(
        string $status
    ): bool {
        return in_array(
            $status,
            [
                ChildLaborer::STATUS_RETURNED,
                ChildLaborer::STATUS_APPROVED,
                ChildLaborer::STATUS_ARCHIVED,
            ],
            true
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function workflowValues(
        string $status,
        int $monthsAgo
    ): array {
        $base = now()
            ->subMonths($monthsAgo)
            ->startOfDay();

        return match ($status) {
            ChildLaborer::STATUS_SUBMITTED => [
                'status' => $status,
                'submitted_at' =>
                    $base->copy()->addDays(4),
                'returned_at' => null,
                'approved_at' => null,
                'archived_at' => null,
                'return_reason' => null,
                'archive_reason' => null,
                'status_before_archive' => null,
            ],

            ChildLaborer::STATUS_RETURNED => [
                'status' => $status,
                'submitted_at' =>
                    $base->copy()->addDays(3),
                'returned_at' =>
                    $base->copy()->addDays(6),
                'approved_at' => null,
                'archived_at' => null,
                'return_reason' =>
                    'Please verify the employer address and current work schedule.',
                'archive_reason' => null,
                'status_before_archive' => null,
            ],

            ChildLaborer::STATUS_APPROVED => [
                'status' => $status,
                'submitted_at' =>
                    $base->copy()->addDays(2),
                'returned_at' => null,
                'approved_at' =>
                    $base->copy()->addDays(7),
                'archived_at' => null,
                'return_reason' => null,
                'archive_reason' => null,
                'status_before_archive' => null,
            ],

            ChildLaborer::STATUS_ARCHIVED => [
                'status' => $status,
                'submitted_at' =>
                    $base->copy()->addDays(2),
                'returned_at' => null,
                'approved_at' =>
                    $base->copy()->addDays(7),
                'archived_at' =>
                    now()->subDays(12),
                'return_reason' => null,
                'archive_reason' =>
                    'Family permanently relocated outside the monitoring area.',
                'status_before_archive' =>
                    ChildLaborer::STATUS_APPROVED,
            ],

            default => [
                'status' => ChildLaborer::STATUS_DRAFT,
                'submitted_at' => null,
                'returned_at' => null,
                'approved_at' => null,
                'archived_at' => null,
                'return_reason' => null,
                'archive_reason' => null,
                'status_before_archive' => null,
            ],
        };
    }

    private function notificationUuid(
        string $value
    ): string {
        $hex = md5(
            self::SEEDED_BY.'|'.$value
        );

        return substr($hex, 0, 8)
            .'-'.substr($hex, 8, 4)
            .'-4'.substr($hex, 13, 3)
            .'-a'.substr($hex, 17, 3)
            .'-'.substr($hex, 20, 12);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function profileRecords(): array
    {
        $currentSchoolYear = '2026-2027';

        return [
            $this->profile(
                number: 'CLPMIS-2026-0001',
                officer: 'profiling@clpmis.test',
                reviewer: 'admin@clpmis.test',
                first: 'Maria',
                middle: 'Lopez',
                last: 'Santos',
                sex: 'Female',
                birthDate: '2011-05-14',
                status: ChildLaborer::STATUS_APPROVED,
                monthsAgo: 5,
                education: $this->education(
                    'Rawis National High School',
                    'Grade 9',
                    $currentSchoolYear,
                    EducationRecord::STATUS_ENROLLED
                ),
                employment: $this->employment(
                    employer: 'Family Street Vending Activity',
                    workType: EmploymentRecord::WORK_PART_TIME,
                    occupation: 'Street food assistant',
                    arrangement: EmploymentRecord::ARRANGEMENT_INFORMAL,
                    days: 3,
                    hours: 3.5,
                    income: 150,
                    hazards: [
                        $this->hazard(
                            'Traffic and Road Exposure',
                            'Works near moving vehicles and roadside smoke.',
                            'Several times per week',
                            unsafe: true
                        ),
                    ]
                ),
                health: $this->health(
                    'Generally healthy',
                    'Occasional cough after roadside work.'
                ),
                interventions: [
                    $this->intervention(
                        Intervention::TYPE_EDUCATIONAL,
                        Intervention::STATUS_COMPLETED,
                        3500
                    ),
                    $this->intervention(
                        Intervention::TYPE_LIVELIHOOD,
                        Intervention::STATUS_ONGOING,
                        8000
                    ),
                ],
                guardian: 'Elena Santos',
                guardianRelationship: 'Mother',
                guardianOccupation: 'Laundry Worker',
                guardianIncome: 6500,
                sibling: 'Miguel Santos',
                street: 'Rizal Street',
                religion: 'Roman Catholic',
                contact: '09171230001',
                hasDocument: true
            ),

            $this->profile(
                number: 'CLPMIS-2026-0002',
                officer: 'profiling@clpmis.test',
                reviewer: 'admin@clpmis.test',
                first: 'Joshua',
                middle: 'Perez',
                last: 'Reyes',
                sex: 'Male',
                birthDate: '2012-02-20',
                status: ChildLaborer::STATUS_SUBMITTED,
                monthsAgo: 4,
                education: $this->education(
                    'Bagumbayan National High School',
                    'Grade 8',
                    $currentSchoolYear,
                    EducationRecord::STATUS_ENROLLED
                ),
                employment: $this->employment(
                    employer: 'Seasonal Vegetable Farm',
                    workType: EmploymentRecord::WORK_SEASONAL,
                    occupation: 'Farm helper',
                    arrangement: EmploymentRecord::ARRANGEMENT_INFORMAL,
                    days: 4,
                    hours: 5,
                    income: 250,
                    hazards: [
                        $this->hazard(
                            'Chemical Exposure',
                            'May be present during pesticide application.',
                            'Occasional',
                            chemicals: 'Agricultural pesticide residue',
                            unsafe: true,
                            ppe: false
                        ),
                    ]
                ),
                health: $this->health(
                    'No diagnosed condition',
                    'Eye irritation during farm activity.'
                ),
                interventions: [
                    $this->intervention(
                        Intervention::TYPE_FOOD,
                        Intervention::STATUS_ONGOING,
                        2500
                    ),
                ],
                guardian: 'Rogelio Reyes',
                guardianRelationship: 'Father',
                guardianOccupation: 'Farm Laborer',
                guardianIncome: 7000,
                sibling: 'Andrea Reyes',
                street: 'Maharlika Road',
                religion: 'Roman Catholic',
                contact: '09171230002',
                hasDocument: true
            ),

            $this->profile(
                number: 'CLPMIS-2026-0003',
                officer: 'profiling2@clpmis.test',
                reviewer: 'admin.audit@clpmis.test',
                first: 'Angel',
                middle: 'M.',
                last: 'Dela Cruz',
                sex: 'Female',
                birthDate: '2013-11-08',
                status: ChildLaborer::STATUS_DRAFT,
                monthsAgo: 3,
                education: $this->education(
                    'Rawis Elementary School',
                    'Grade 6',
                    $currentSchoolYear,
                    EducationRecord::STATUS_ENROLLED
                ),
                employment: null,
                health: $this->health(
                    'Generally healthy',
                    null
                ),
                interventions: [],
                guardian: 'Lorna Dela Cruz',
                guardianRelationship: 'Mother',
                guardianOccupation: 'Home-based Food Seller',
                guardianIncome: 5500,
                sibling: 'Paolo Dela Cruz',
                street: 'Peñaranda Street',
                religion: 'Christian',
                contact: '09171230003',
                hasDocument: false
            ),

            $this->profile(
                number: 'CLPMIS-2026-0004',
                officer: 'profiling@clpmis.test',
                reviewer: 'admin@clpmis.test',
                first: 'Carlo',
                middle: 'Diaz',
                last: 'Mendoza',
                sex: 'Male',
                birthDate: '2010-04-02',
                status: ChildLaborer::STATUS_RETURNED,
                monthsAgo: 3,
                education: $this->education(
                    'Alternative Learning System Center',
                    'ALS Junior High School',
                    $currentSchoolYear,
                    EducationRecord::STATUS_ENROLLED
                ),
                employment: $this->employment(
                    employer: 'Small Construction Group',
                    workType: EmploymentRecord::WORK_ON_CALL,
                    occupation: 'Construction assistant',
                    arrangement: EmploymentRecord::ARRANGEMENT_INFORMAL,
                    days: 4,
                    hours: 7,
                    income: 450,
                    hazards: [
                        $this->hazard(
                            'Heavy Work and Falling Objects',
                            'Carries construction materials and works around unsecured tools.',
                            'Weekly',
                            heavy: true,
                            unsafe: true,
                            ppe: false
                        ),
                    ]
                ),
                health: $this->health(
                    'Recurrent lower back pain',
                    'Back pain after carrying construction materials.'
                ),
                interventions: [
                    $this->intervention(
                        Intervention::TYPE_MEDICAL,
                        Intervention::STATUS_PENDING,
                        1800
                    ),
                    $this->intervention(
                        Intervention::TYPE_SKILLS,
                        Intervention::STATUS_PENDING,
                        null
                    ),
                ],
                guardian: 'Marlon Mendoza',
                guardianRelationship: 'Uncle',
                guardianOccupation: 'Construction Worker',
                guardianIncome: 9000,
                sibling: 'Jessa Mendoza',
                street: 'Yashano Street',
                religion: 'Roman Catholic',
                contact: '09171230004',
                hasDocument: true
            ),

            $this->profile(
                number: 'CLPMIS-2026-0005',
                officer: 'profiling2@clpmis.test',
                reviewer: 'admin.audit@clpmis.test',
                first: 'Nica',
                middle: 'Torres',
                last: 'Villanueva',
                sex: 'Female',
                birthDate: '2012-12-19',
                status: ChildLaborer::STATUS_APPROVED,
                monthsAgo: 2,
                education: $this->education(
                    null,
                    'Grade 7',
                    $currentSchoolYear,
                    EducationRecord::STATUS_DROPPED_OUT,
                    'Household income was insufficient for transportation and school needs.'
                ),
                employment: $this->employment(
                    employer: 'Private Household',
                    workType: EmploymentRecord::WORK_OCCASIONAL,
                    occupation: 'Domestic helper',
                    arrangement: EmploymentRecord::ARRANGEMENT_INFORMAL,
                    days: 3,
                    hours: 5,
                    income: 200,
                    hazards: [
                        $this->hazard(
                            'Cleaning Chemical Exposure',
                            'Uses concentrated cleaning products without gloves.',
                            'Several times per week',
                            chemicals: 'Bleach and cleaning agents',
                            unsafe: true,
                            ppe: false
                        ),
                    ]
                ),
                health: $this->health(
                    'Skin irritation',
                    'Hand irritation after cleaning work.'
                ),
                interventions: [
                    $this->intervention(
                        Intervention::TYPE_EDUCATIONAL,
                        Intervention::STATUS_ONGOING,
                        5000
                    ),
                    $this->intervention(
                        Intervention::TYPE_PSYCHOSOCIAL,
                        Intervention::STATUS_ONGOING,
                        null
                    ),
                ],
                guardian: 'Rosalinda Villanueva',
                guardianRelationship: 'Grandmother',
                guardianOccupation: 'None',
                guardianIncome: 3200,
                sibling: 'Noel Villanueva',
                street: 'Quezon Avenue',
                religion: 'Roman Catholic',
                contact: '09171230005',
                hasDocument: true
            ),

            $this->profile(
                number: 'CLPMIS-2026-0006',
                officer: 'profiling@clpmis.test',
                reviewer: 'admin@clpmis.test',
                first: 'Mark',
                middle: 'Ocampo',
                last: 'Bautista',
                sex: 'Male',
                birthDate: '2008-09-30',
                status: ChildLaborer::STATUS_ARCHIVED,
                monthsAgo: 5,
                education: $this->education(
                    null,
                    'Grade 8',
                    '2025-2026',
                    EducationRecord::STATUS_NOT_ENROLLED,
                    'Stopped attending school to support household fishing activities.'
                ),
                employment: $this->employment(
                    employer: 'Family Fishing Activity',
                    workType: EmploymentRecord::WORK_FULL_TIME,
                    occupation: 'Fishing assistant',
                    arrangement: EmploymentRecord::ARRANGEMENT_FAMILY_WORK,
                    days: 6,
                    hours: 9,
                    income: null,
                    hazards: [
                        $this->hazard(
                            'Open Water and Night Work',
                            'Works on a small fishing boat before sunrise.',
                            'Daily',
                            longHours: true,
                            night: true,
                            unsafe: true,
                            ppe: false
                        ),
                    ]
                ),
                health: $this->health(
                    'Sleep deprivation and fatigue',
                    'Persistent fatigue after night fishing.'
                ),
                interventions: [
                    $this->intervention(
                        Intervention::TYPE_REFERRAL,
                        Intervention::STATUS_DISCONTINUED,
                        null
                    ),
                ],
                guardian: 'Dante Bautista',
                guardianRelationship: 'Father',
                guardianOccupation: 'Fisherman',
                guardianIncome: 8500,
                sibling: 'Mika Bautista',
                street: 'Coastal Road',
                religion: 'Roman Catholic',
                contact: '09171230006',
                hasDocument: false
            ),

            $this->profile(
                number: 'CLPMIS-2026-0007',
                officer: 'profiling2@clpmis.test',
                reviewer: 'admin.audit@clpmis.test',
                first: 'Sofia',
                middle: 'Garcia',
                last: 'Ramos',
                sex: 'Female',
                birthDate: '2014-10-12',
                status: ChildLaborer::STATUS_APPROVED,
                monthsAgo: 1,
                education: $this->education(
                    'Rawis Elementary School',
                    'Grade 5',
                    $currentSchoolYear,
                    EducationRecord::STATUS_ENROLLED
                ),
                employment: $this->employment(
                    employer: 'Family Food Stall',
                    workType: EmploymentRecord::WORK_UNPAID_FAMILY,
                    occupation: 'Food stall helper',
                    arrangement: EmploymentRecord::ARRANGEMENT_FAMILY_WORK,
                    days: 2,
                    hours: 2.5,
                    income: null,
                    hazards: [
                        $this->hazard(
                            'Heat and Hot Surfaces',
                            'Works near cooking equipment and hot containers.',
                            'Twice per week',
                            unsafe: true,
                            ppe: true
                        ),
                    ]
                ),
                health: $this->health(
                    'Generally healthy',
                    null
                ),
                interventions: [
                    $this->intervention(
                        Intervention::TYPE_FOOD,
                        Intervention::STATUS_COMPLETED,
                        2200
                    ),
                ],
                guardian: 'Joy Ramos',
                guardianRelationship: 'Mother',
                guardianOccupation: 'Food Vendor',
                guardianIncome: 7200,
                sibling: 'Lucas Ramos',
                street: 'Imelda Roces Avenue',
                religion: 'Christian',
                contact: '09171230007',
                hasDocument: true
            ),

            $this->profile(
                number: 'CLPMIS-2026-0008',
                officer: 'profiling@clpmis.test',
                reviewer: 'admin@clpmis.test',
                first: 'Kevin',
                middle: 'Soriano',
                last: 'Garcia',
                sex: 'Male',
                birthDate: '2011-01-25',
                status: ChildLaborer::STATUS_SUBMITTED,
                monthsAgo: 1,
                education: $this->education(
                    null,
                    'Grade 7',
                    $currentSchoolYear,
                    EducationRecord::STATUS_NOT_ENROLLED,
                    'Left school because of household debt and daily work.'
                ),
                employment: $this->employment(
                    employer: 'Independent Collection Activity',
                    workType: EmploymentRecord::WORK_PIECE_RATE,
                    occupation: 'Recyclable material collector',
                    arrangement: EmploymentRecord::ARRANGEMENT_SELF_EMPLOYED,
                    days: 6,
                    hours: 6,
                    income: 180,
                    hazards: [
                        $this->hazard(
                            'Sharp Objects and Waste Exposure',
                            'Handles mixed waste and sharp discarded materials.',
                            'Daily',
                            chemicals: 'Mixed household waste',
                            unsafe: true,
                            ppe: false,
                            injuries: 'Minor hand cuts reported.'
                        ),
                    ]
                ),
                health: $this->health(
                    'Minor recurring hand wounds',
                    'Hand cuts and occasional fever.'
                ),
                interventions: [
                    $this->intervention(
                        Intervention::TYPE_RESCUE,
                        Intervention::STATUS_PENDING,
                        null
                    ),
                    $this->intervention(
                        Intervention::TYPE_SOCIAL_PROTECTION,
                        Intervention::STATUS_PENDING,
                        null
                    ),
                ],
                guardian: 'Liza Garcia',
                guardianRelationship: 'Mother',
                guardianOccupation: 'Waste Collector',
                guardianIncome: 5000,
                sibling: 'Francis Garcia',
                street: 'Daraga-Legazpi Diversion Road',
                religion: 'Roman Catholic',
                contact: '09171230008',
                hasDocument: true
            ),

            $this->profile(
                number: 'CLPMIS-2026-0009',
                officer: 'profiling2@clpmis.test',
                reviewer: 'admin.audit@clpmis.test',
                first: 'Alyssa',
                middle: 'Cruz',
                last: 'Flores',
                sex: 'Female',
                birthDate: '2015-08-16',
                status: ChildLaborer::STATUS_DRAFT,
                monthsAgo: 0,
                education: $this->education(
                    'Rawis Elementary School',
                    'Grade 4',
                    $currentSchoolYear,
                    EducationRecord::STATUS_ENROLLED
                ),
                employment: null,
                health: $this->health(
                    'Generally healthy',
                    null
                ),
                interventions: [],
                guardian: 'Marites Flores',
                guardianRelationship: 'Mother',
                guardianOccupation: 'Market Vendor',
                guardianIncome: 6100,
                sibling: 'Ethan Flores',
                street: 'Taysan Road',
                religion: 'Roman Catholic',
                contact: '09171230009',
                hasDocument: false
            ),

            $this->profile(
                number: 'CLPMIS-2026-0010',
                officer: 'profiling@clpmis.test',
                reviewer: 'admin@clpmis.test',
                first: 'Jerome',
                middle: 'A.',
                last: 'Navarro',
                sex: 'Male',
                birthDate: '2010-03-07',
                status: ChildLaborer::STATUS_APPROVED,
                monthsAgo: 0,
                education: $this->education(
                    'Alternative Learning System Center',
                    'ALS Junior High School',
                    $currentSchoolYear,
                    EducationRecord::STATUS_ENROLLED
                ),
                employment: $this->employment(
                    employer: 'Neighborhood Vulcanizing Shop',
                    workType: EmploymentRecord::WORK_PART_TIME,
                    occupation: 'Vulcanizing shop assistant',
                    arrangement: EmploymentRecord::ARRANGEMENT_INFORMAL,
                    days: 5,
                    hours: 5,
                    income: 300,
                    hazards: [
                        $this->hazard(
                            'Machinery, Heat, and Chemical Exposure',
                            'Handles tire repair tools, adhesives, and heated materials.',
                            'Daily',
                            equipment: 'Air compressor and tire tools',
                            chemicals: 'Rubber cement and cleaning solvent',
                            unsafe: true,
                            ppe: true
                        ),
                    ]
                ),
                health: $this->health(
                    'Occasional headache',
                    'Headache after exposure to rubber cement fumes.'
                ),
                interventions: [
                    $this->intervention(
                        Intervention::TYPE_SKILLS,
                        Intervention::STATUS_ONGOING,
                        6000
                    ),
                    $this->intervention(
                        Intervention::TYPE_EMPLOYMENT,
                        Intervention::STATUS_PENDING,
                        null
                    ),
                ],
                guardian: 'Edgar Navarro',
                guardianRelationship: 'Father',
                guardianOccupation: 'Tricycle Driver',
                guardianIncome: 8800,
                sibling: 'Angela Navarro',
                street: 'Washington Drive',
                religion: 'Roman Catholic',
                contact: '09171230010',
                hasDocument: true
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function profile(
        string $number,
        string $officer,
        string $reviewer,
        string $first,
        ?string $middle,
        string $last,
        string $sex,
        string $birthDate,
        string $status,
        int $monthsAgo,
        array $education,
        ?array $employment,
        array $health,
        array $interventions,
        string $guardian,
        string $guardianRelationship,
        string $guardianOccupation,
        float $guardianIncome,
        string $sibling,
        string $street,
        string $religion,
        string $contact,
        bool $hasDocument
    ): array {
        return [
            'profile_number' => $number,
            'officer_email' => $officer,
            'reviewer_email' => $reviewer,
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
            'suffix' => null,
            'sex' => $sex,
            'birth_date' => $birthDate,
            'status' => $status,
            'created_months_ago' => $monthsAgo,
            'education' => $education,
            'employment' => $employment,
            'health' => $health,
            'interventions' => $interventions,
            'guardian_name' => $guardian,
            'guardian_relationship' =>
                $guardianRelationship,
            'guardian_occupation' =>
                $guardianOccupation,
            'guardian_income' =>
                $guardianIncome,
            'sibling_name' => $sibling,
            'street' => $street,
            'religion' => $religion,
            'contact_number' => $contact,
            'has_document' => $hasDocument,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function education(
        ?string $school,
        string $level,
        string $schoolYear,
        string $status,
        ?string $reason = null
    ): array {
        return [
            'school_name' => $school,
            'grade_year_level' => $level,
            'school_year' => $schoolYear,
            'school_address' =>
                $school
                    ? 'Legazpi City, Albay'
                    : null,
            'enrollment_status' => $status,
            'reason_not_attending' => $reason,
            'last_grade_completed' =>
                $status === EducationRecord::STATUS_ENROLLED
                    ? null
                    : $level,
            'date_enrolled' =>
                $status === EducationRecord::STATUS_ENROLLED
                    ? '2026-06-01'
                    : null,
            'date_ended' => null,
            'is_current' => true,
            'remarks' =>
                'Synthetic demonstration education record.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function employment(
        string $employer,
        string $workType,
        string $occupation,
        string $arrangement,
        int $days,
        float $hours,
        ?float $income,
        array $hazards
    ): array {
        return [
            'employer_name' => $employer,
            'employer_address' =>
                'Legazpi City, Albay',
            'work_type' => $workType,
            'occupation' => $occupation,
            'industry' =>
                'Informal Local Economy',
            'employment_arrangement' =>
                $arrangement,
            'start_date' =>
                '2026-01-15',
            'end_date' => null,
            'days_per_week' => $days,
            'hours_per_day' => $hours,
            'income_amount' => $income,
            'income_frequency' =>
                $income === null
                    ? 'Not Applicable'
                    : 'Daily',
            'is_current' => true,
            'remarks' =>
                'Synthetic demonstration employment record.',
            'hazards' => $hazards,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function hazard(
        string $type,
        string $description,
        string $frequency,
        ?string $equipment = null,
        ?string $chemicals = null,
        bool $heavy = false,
        bool $longHours = false,
        bool $night = false,
        bool $unsafe = false,
        bool $ppe = false,
        ?string $injuries = null
    ): array {
        return [
            'hazard_type' => $type,
            'hazard_description' => $description,
            'exposure_frequency' => $frequency,
            'equipment_machinery' => $equipment,
            'chemicals_substances' => $chemicals,
            'heavy_work' => $heavy,
            'long_hours' => $longHours,
            'night_work' => $night,
            'unsafe_conditions' => $unsafe,
            'ppe_provided' => $ppe,
            'ppe_description' =>
                $ppe
                    ? 'Basic gloves or protective covering provided.'
                    : null,
            'injuries_incidents' => $injuries,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function health(
        ?string $condition,
        ?string $complaint
    ): array {
        return [
            'assessment_date' =>
                '2026-07-01',
            'health_condition' => $condition,
            'has_disability' => false,
            'disability_details' => null,
            'injury_history' => null,
            'treatment_received' =>
                $complaint
                    ? 'Initial barangay health assessment.'
                    : null,
            'health_facility' =>
                'Barangay Health Center',
            'current_complaints' => $complaint,
            'mental_health_concerns' => null,
            'remarks' =>
                'Synthetic demonstration health assessment.',
            'is_current' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function intervention(
        string $type,
        string $status,
        ?float $amount
    ): array {
        return [
            'intervention_type' => $type,
            'provider' =>
                'DOLE Regional Office V and Partner Agency',
            'description' =>
                'Demonstration assistance recorded for workflow and reporting tests.',
            'date_provided' =>
                in_array(
                    $status,
                    [
                        Intervention::STATUS_ONGOING,
                        Intervention::STATUS_COMPLETED,
                        Intervention::STATUS_DISCONTINUED,
                    ],
                    true
                )
                    ? '2026-06-15'
                    : null,
            'date_completed' =>
                $status === Intervention::STATUS_COMPLETED
                    ? '2026-07-10'
                    : null,
            'amount' => $amount,
            'status' => $status,
            'remarks' =>
                'Synthetic demonstration intervention.',
        ];
    }
}
