<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ChildLaborer;
use App\Models\Province;
use App\Models\Region;
use App\Services\ActivityLogger;
use App\Services\Reports\ChildLaborerReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChildLaborerReportController extends Controller
{
    public function __construct(
        private readonly ChildLaborerReportService
            $reportService,

        private readonly ActivityLogger
            $activityLogger
    ) {
    }

    public function index(
        Request $request
    ): View {
        Gate::authorize(
            'view-reports'
        );

        $filters = $this->reportService
            ->filters($request);

        $baseQuery = $this->reportService
            ->query(
                $filters,
                $request->user()
            );

        $summary = [
            'total' => (
                clone $baseQuery
            )->count(),

            'male' => (
                clone $baseQuery
            )
                ->where(
                    'sex',
                    'Male'
                )
                ->count(),

            'female' => (
                clone $baseQuery
            )
                ->where(
                    'sex',
                    'Female'
                )
                ->count(),

            'currently_working' => (
                clone $baseQuery
            )
                ->whereHas(
                    'currentEmployment'
                )
                ->count(),

            'with_interventions' => (
                clone $baseQuery
            )
                ->whereHas(
                    'interventions'
                )
                ->count(),
        ];

        $childLaborers = $baseQuery
            ->paginate(25)
            ->withQueryString()
            ->through(
                fn (ChildLaborer $childLaborer): array =>
                    $this->reportService
                        ->masterRow(
                            $childLaborer
                        )
            );

        $regions = Region::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $provinces = Province::query()
            ->when(
                $filters['region_id'],
                fn ($query) =>
                    $query->where(
                        'region_id',
                        $filters[
                            'region_id'
                        ]
                    )
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'region_id',
            ]);

        return view(
            'reports.child-laborers.index',
            [
                'childLaborers' =>
                    $childLaborers,

                'summary' =>
                    $summary,

                'filters' =>
                    $filters,

                'regions' =>
                    $regions,

                'provinces' =>
                    $provinces,

                'statusOptions' =>
                    $this->reportService
                        ->statusOptions(),
            ]
        );
    }

    public function exportCsv(
        Request $request
    ): StreamedResponse {
        Gate::authorize(
            'export-reports'
        );

        $filters = $this->reportService
            ->filters($request);

        $query = $this->reportService
            ->query(
                $filters,
                $request->user()
            );

        $recordCount = (
            clone $query
        )->count();

        $this->activityLogger->log(
            action: 'exported',
            description:
                'Exported the filtered child laborer master list as CSV.',
            metadata: [
                'report' =>
                    'Child Laborer Master List',

                'format' =>
                    'CSV',

                'record_count' =>
                    $recordCount,

                'filters' =>
                    $filters,
            ],
            actor: $request->user()
        );

        $filename =
            'child-laborer-master-list-'
            .now()->format('Ymd-His')
            .'.csv';

        return response()->streamDownload(
            function () use ($query): void {
                $handle = fopen(
                    'php://output',
                    'wb'
                );

                if ($handle === false) {
                    throw new RuntimeException(
                        'Unable to open the CSV output stream.'
                    );
                }

                /*
                 * UTF-8 BOM helps Microsoft Excel display
                 * Philippine names and symbols correctly.
                 */
                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );

                fputcsv(
                    $handle,
                    [
                        'Profile Number',
                        'Full Name',
                        'Sex',
                        'Birth Date',
                        'Age',
                        'Status',
                        'Region',
                        'Province',
                        'City / Municipality',
                        'Barangay',
                        'Complete Address',
                        'Primary Guardian',
                        'Guardian Contact',
                        'Education Status',
                        'Grade / Year Level',
                        'School',
                        'Currently Working',
                        'Occupation',
                        'Employer',
                        'Interventions',
                        'Assigned Officer',
                        'Profile Created',
                    ],
                    ',',
                    '"',
                    ''
                );

                $query->chunk(
                    500,
                    function (
                        $childLaborers
                    ) use ($handle): void {
                        foreach (
                            $childLaborers
                            as $childLaborer
                        ) {
                            $row = $this
                                ->reportService
                                ->masterRow(
                                    $childLaborer
                                );

                            fputcsv(
                                $handle,
                                array_map(
                                    fn (
                                        mixed $value
                                    ): string =>
                                        $this
                                            ->safeCsvValue(
                                                $value
                                            ),
                                    [
                                        $row[
                                            'profile_number'
                                        ],
                                        $row[
                                            'full_name'
                                        ],
                                        $row['sex'],
                                        $row[
                                            'birth_date'
                                        ],
                                        $row['age'],
                                        $row['status'],
                                        $row['region'],
                                        $row['province'],
                                        $row['locality'],
                                        $row['barangay'],
                                        $row['address'],
                                        $row[
                                            'guardian_name'
                                        ],
                                        $row[
                                            'guardian_contact'
                                        ],
                                        $row[
                                            'education_status'
                                        ],
                                        $row[
                                            'grade_year_level'
                                        ],
                                        $row[
                                            'school_name'
                                        ],
                                        $row[
                                            'currently_working'
                                        ],
                                        $row[
                                            'occupation'
                                        ],
                                        $row[
                                            'employer_name'
                                        ],
                                        $row[
                                            'interventions_count'
                                        ],
                                        $row[
                                            'assigned_officer'
                                        ],
                                        $row[
                                            'created_at'
                                        ],
                                    ]
                                ),
                                ',',
                                '"',
                                ''
                            );
                        }
                    }
                );

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',

                'Cache-Control' =>
                    'private, no-store, no-cache',
            ]
        );
    }

    public function printMasterList(
        Request $request
    ): View {
        Gate::authorize(
            'print-reports'
        );

        $filters = $this->reportService
            ->filters($request);

        $records = $this->reportService
            ->query(
                $filters,
                $request->user()
            )
            ->get();

        $rows = $records->map(
            fn (
                ChildLaborer $childLaborer
            ): array =>
                $this->reportService
                    ->masterRow(
                        $childLaborer
                    )
        );

        $this->activityLogger->log(
            action: 'printed',
            description:
                'Opened the filtered child laborer master list for printing.',
            metadata: [
                'report' =>
                    'Child Laborer Master List',

                'record_count' =>
                    $rows->count(),

                'filters' =>
                    $filters,
            ],
            actor: $request->user()
        );

        return view(
            'reports.child-laborers.master-print',
            [
                'rows' => $rows,

                'filters' => $filters,

                'printedAt' => now(),

                'printedBy' =>
                    $request->user()->name,
            ]
        );
    }

    public function profile(
        Request $request,
        ChildLaborer $childLaborer
    ): View {
        Gate::authorize(
            'view-reports'
        );

        abort_unless(
            $this->reportService
                ->canViewProfileReport(
                    $request->user(),
                    $childLaborer
                ),
            403
        );

        $this->reportService
            ->loadProfileReport(
                $childLaborer,
                $request->user()
            );

        return view(
            'reports.child-laborers.profile',
            [
                'childLaborer' =>
                    $childLaborer,

                'includeSensitive' =>
                    $this->reportService
                        ->includeSensitiveSections(
                            $request->user()
                        ),

                'photoDataUri' =>
                    $this->reportService
                        ->photoDataUri(
                            $childLaborer
                        ),

                'addressText' =>
                    $this->reportService
                        ->addressText(
                            $childLaborer
                                ->residentialAddress
                        ),

                'generatedAt' =>
                    now(),

                'generatedBy' =>
                    $request->user()->name,
            ]
        );
    }

    public function printProfile(
        Request $request,
        ChildLaborer $childLaborer
    ): View {
        Gate::authorize(
            'print-reports'
        );

        abort_unless(
            $this->reportService
                ->canViewProfileReport(
                    $request->user(),
                    $childLaborer
                ),
            403
        );

        $this->reportService
            ->loadProfileReport(
                $childLaborer,
                $request->user()
            );

        $this->activityLogger->log(
            action: 'printed',
            description:
                'Opened the comprehensive child laborer profile report for printing.',
            subject: $childLaborer,
            metadata: [
                'report' =>
                    'Comprehensive Child Laborer Profile',

                'profile_number' =>
                    $childLaborer
                        ->profile_number,

                'sensitive_sections_included' =>
                    $this->reportService
                        ->includeSensitiveSections(
                            $request->user()
                        ),
            ],
            actor: $request->user()
        );

        return view(
            'reports.child-laborers.profile-print',
            [
                'childLaborer' =>
                    $childLaborer,

                'includeSensitive' =>
                    $this->reportService
                        ->includeSensitiveSections(
                            $request->user()
                        ),

                'photoDataUri' =>
                    $this->reportService
                        ->photoDataUri(
                            $childLaborer
                        ),

                'addressText' =>
                    $this->reportService
                        ->addressText(
                            $childLaborer
                                ->residentialAddress
                        ),

                'generatedAt' =>
                    now(),

                'generatedBy' =>
                    $request->user()->name,
            ]
        );
    }

    private function safeCsvValue(
        mixed $value
    ): string {
        $value = trim(
            (string) ($value ?? '')
        );

        /*
         * Prevent spreadsheet formula injection when CSV files
         * are opened in Excel or another spreadsheet program.
         */
        if (
            $value !== ''
            && in_array(
                $value[0],
                [
                    '=',
                    '+',
                    '-',
                    '@',
                ],
                true
            )
        ) {
            return "'".$value;
        }

        return $value;
    }
}