<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\Region;
use App\Services\ActivityLogger;
use App\Services\Reports\StatisticalReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatisticalReportController extends Controller
{
    public function __construct(
        private readonly StatisticalReportService
            $reportService,

        private readonly ActivityLogger
            $activityLogger
    ) {
    }

    public function index(Request $request): View
    {
        Gate::authorize('view-reports');

        $filters = $this->reportService
            ->filters($request);

        $report = $this->reportService
            ->build(
                $filters,
                $request->user()
            );

        return view(
            'reports.statistics.index',
            [
                ...$this->sharedViewData(
                    $filters,
                    $report
                ),
            ]
        );
    }

    public function print(Request $request): View
    {
        Gate::authorize('print-reports');

        $filters = $this->reportService
            ->filters($request);

        $report = $this->reportService
            ->build(
                $filters,
                $request->user()
            );

        $this->activityLogger->log(
            action: 'printed',
            description:
                'Opened the statistical and summary report for printing.',
            metadata: [
                'report' =>
                    'Statistical and Summary Report',

                'filters' =>
                    $filters,

                'total_profiles' =>
                    $report['summary']['total_profiles'],
            ],
            actor: $request->user()
        );

        return view(
            'reports.statistics.print',
            [
                ...$this->sharedViewData(
                    $filters,
                    $report
                ),

                'generatedAt' => now(),

                'generatedBy' =>
                    $request->user()->name,
            ]
        );
    }

    public function exportCsv(
        Request $request
    ): StreamedResponse {
        Gate::authorize('export-reports');

        $filters = $this->reportService
            ->filters($request);

        $report = $this->reportService
            ->build(
                $filters,
                $request->user()
            );

        $this->activityLogger->log(
            action: 'exported',
            description:
                'Exported the statistical and summary report as CSV.',
            metadata: [
                'report' =>
                    'Statistical and Summary Report',

                'format' =>
                    'CSV',

                'filters' =>
                    $filters,

                'total_profiles' =>
                    $report['summary']['total_profiles'],
            ],
            actor: $request->user()
        );

        $filename =
            'clpmis-statistical-report-'
            .now()->format('Ymd-His')
            .'.csv';

        return response()->streamDownload(
            function () use ($report): void {
                $handle = fopen(
                    'php://output',
                    'wb'
                );

                if ($handle === false) {
                    throw new RuntimeException(
                        'Unable to open the CSV output stream.'
                    );
                }

                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv(
                    $handle,
                    [
                        'Section',
                        'Metric',
                        'Value',
                        'Percentage',
                    ],
                    ',',
                    '"',
                    ''
                );

                foreach (
                    $this->csvRows($report)
                    as $row
                ) {
                    fputcsv(
                        $handle,
                        $row,
                        ',',
                        '"',
                        ''
                    );
                }

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

    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $report
     *
     * @return array<string, mixed>
     */
    private function sharedViewData(
        array $filters,
        array $report
    ): array {
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
                        $filters['region_id']
                    )
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'region_id',
            ]);

        return [
            'filters' => $filters,

            'report' => $report,

            'regions' => $regions,

            'provinces' => $provinces,

            'statusOptions' =>
                $this->reportService
                    ->statusOptions(),
        ];
    }

    /**
     * @param array<string, mixed> $report
     *
     * @return array<int, array<int, string|int|float>>
     */
    private function csvRows(array $report): array
    {
        $rows = [];

        foreach (
            [
                'Total Profiles' =>
                    $report['summary']['total_profiles'],

                'Currently Working' =>
                    $report['summary']['currently_working'],

                'Profiles With Hazards' =>
                    $report['summary']['with_hazards'],

                'Profiles With Interventions' =>
                    $report['summary']['with_interventions'],

                'Profiles With Completed Audits' =>
                    $report['summary']['completed_audits'],

                'Total Intervention Value' =>
                    $report['summary']['intervention_value'],
            ]
            as $label => $value
        ) {
            $rows[] = [
                'Summary',
                $label,
                $value,
                '',
            ];
        }

        $sections = [
            'Sex Distribution' =>
                $report['sexDistribution'],

            'Age Distribution' =>
                $report['ageDistribution'],

            'Profile Status' =>
                $report['statusDistribution'],

            'Profile Creation Trend' =>
                $report['profileTrend'],

            'Region' =>
                $report['regions'],

            'Province' =>
                $report['provinces'],

            'Education' =>
                $report['education'],

            'Employment' =>
                $report['employment'],

            'Work Type' =>
                $report['workTypes'],

            'Work Hazards' =>
                $report['hazards'],

            'Intervention Type' =>
                $report['interventionTypes'],

            'Intervention Status' =>
                $report['interventionStatuses'],

            'Audit Schedule Status' =>
                $report['auditScheduleStatuses'],

            'Audit Evaluation Status' =>
                $report['auditEvaluationStatuses'],
        ];

        foreach ($sections as $section => $items) {
            foreach ($items as $item) {
                $rows[] = [
                    $section,
                    $this->safeCsvValue(
                        $item['label']
                    ),
                    $item['total'],
                    $item['percentage'],
                ];
            }
        }

        return $rows;
    }

    private function safeCsvValue(
        mixed $value
    ): string {
        $value = trim(
            (string) ($value ?? '')
        );

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
