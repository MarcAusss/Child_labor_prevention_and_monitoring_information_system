<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        CLPMIS Statistical and Summary Report
    </title>

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 14px;
        }

        .toolbar button,
        .toolbar a {
            border: 0;
            border-radius: 6px;
            padding: 9px 14px;
            background: #0284c7;
            color: white;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 10px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 5px 0 0;
            color: #475569;
        }

        .meta,
        .summary {
            display: grid;
            gap: 7px;
            margin-top: 10px;
        }

        .meta {
            grid-template-columns: repeat(4, 1fr);
        }

        .summary {
            grid-template-columns: repeat(6, 1fr);
        }

        .meta div,
        .summary div {
            border: 1px solid #cbd5e1;
            padding: 7px;
        }

        .summary span {
            display: block;
            color: #64748b;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary strong {
            display: block;
            margin-top: 4px;
            font-size: 13px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 12px;
        }

        .section {
            border: 1px solid #cbd5e1;
            page-break-inside: avoid;
        }

        .section h2 {
            margin: 0;
            background: #e0f2fe;
            padding: 7px;
            color: #075985;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border-top: 1px solid #e2e8f0;
            padding: 5px 7px;
            text-align: left;
        }

        th {
            color: #475569;
            font-size: 7px;
            text-transform: uppercase;
        }

        td {
            font-size: 8px;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .footer {
            margin-top: 12px;
            color: #64748b;
            font-size: 7px;
            text-align: right;
        }

        @media print {
            .toolbar {
                display: none;
            }
        }
    </style>
</head>

<body>
    @php
        $sections = [
            'Sex Distribution' =>
                $report['sexDistribution'],

            'Age Distribution' =>
                $report['ageDistribution'],

            'Profile Status' =>
                $report['statusDistribution'],

            'Profile Creation Trend' =>
                $report['profileTrend'],

            'Profiles by Region' =>
                $report['regions'],

            'Profiles by Province' =>
                $report['provinces'],

            'Current Education Status' =>
                $report['education'],

            'Employment Coverage' =>
                $report['employment'],

            'Current Work Types' =>
                $report['workTypes'],

            'Recorded Work Hazards' =>
                $report['hazards'],

            'Intervention Types' =>
                $report['interventionTypes'],

            'Intervention Status' =>
                $report['interventionStatuses'],

            'Audit Schedule Status' =>
                $report['auditScheduleStatuses'],

            'Audit Evaluation Status' =>
                $report['auditEvaluationStatuses'],
        ];
    @endphp

    <div class="toolbar">
        <button
            type="button"
            onclick="window.print()"
        >
            Print Report
        </button>

        <a
            href="{{ route(
                'reports.statistics.index',
                request()->query()
            ) }}"
        >
            Back
        </a>
    </div>

    <header class="header">
        <h1>
            Statistical and Summary Report
        </h1>

        <p>
            Child Labor Prevention and Monitoring
            Information System
        </p>

        <p>
            Generated
            {{ $generatedAt->format(
                'F d, Y h:i A'
            ) }}
            by {{ $generatedBy }}
        </p>
    </header>

    <section class="meta">
        <div>
            <strong>Status:</strong>
            {{ $filters['status']
                ?: 'All permitted statuses' }}
        </div>

        <div>
            <strong>Region ID:</strong>
            {{ $filters['region_id']
                ?: 'All regions' }}
        </div>

        <div>
            <strong>Province ID:</strong>
            {{ $filters['province_id']
                ?: 'All provinces' }}
        </div>

        <div>
            <strong>Date range:</strong>
            {{ $filters['from']
                ?: 'Beginning' }}
            to
            {{ $filters['to']
                ?: 'Present' }}
        </div>
    </section>

    <section class="summary">
        <div>
            <span>Total Profiles</span>

            <strong>
                {{ number_format(
                    $report['summary']['total_profiles']
                ) }}
            </strong>
        </div>

        <div>
            <span>Currently Working</span>

            <strong>
                {{ number_format(
                    $report['summary']['currently_working']
                ) }}
            </strong>
        </div>

        <div>
            <span>With Hazards</span>

            <strong>
                {{ number_format(
                    $report['summary']['with_hazards']
                ) }}
            </strong>
        </div>

        <div>
            <span>With Interventions</span>

            <strong>
                {{ number_format(
                    $report['summary']['with_interventions']
                ) }}
            </strong>
        </div>

        <div>
            <span>Completed Audits</span>

            <strong>
                {{ number_format(
                    $report['summary']['completed_audits']
                ) }}
            </strong>
        </div>

        <div>
            <span>Intervention Value</span>

            <strong>
                ₱{{ number_format(
                    $report['summary']['intervention_value'],
                    2
                ) }}
            </strong>
        </div>
    </section>

    <main class="grid">
        @foreach ($sections as $title => $items)
            <section class="section">
                <h2>
                    {{ $title }}
                </h2>

                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="number">Count</th>
                            <th class="number">Percent</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>
                                    {{ $item['label'] }}
                                </td>

                                <td class="number">
                                    {{ number_format(
                                        $item['total']
                                    ) }}
                                </td>

                                <td class="number">
                                    {{ number_format(
                                        $item['percentage'],
                                        2
                                    ) }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    No data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        @endforeach
    </main>

    <footer class="footer">
        CLPMIS · Statistical and Summary Report ·
        {{ $generatedAt->format(
            'Y-m-d H:i:s'
        ) }}
    </footer>
</body>
</html>
