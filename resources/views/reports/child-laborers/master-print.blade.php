<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Child Laborer Master List
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
            font-size: 10px;
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
            margin-bottom: 14px;
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

        .meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }

        .meta div {
            border: 1px solid #cbd5e1;
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #94a3b8;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #e0f2fe;
            color: #075985;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
        }

        td {
            font-size: 8px;
            line-height: 1.35;
        }

        .nowrap {
            white-space: nowrap;
        }

        .footer {
            margin-top: 12px;
            color: #64748b;
            font-size: 8px;
            text-align: right;
        }

        @media print {
            .toolbar {
                display: none;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <button
            type="button"
            onclick="window.print()"
        >
            Print Report
        </button>

        <a
            href="{{ route(
                'reports.child-laborers.index',
                request()->query()
            ) }}"
        >
            Back
        </a>
    </div>

    <header class="header">
        <p>
            Child Labor Prevention and Monitoring Information System
        </p>

        <h1>
            Child Laborer Master List
        </h1>

        <p>
            Filtered report generated
            {{ $printedAt->format(
                'F d, Y h:i A'
            ) }}
        </p>
    </header>

    <section class="meta">
        <div>
            <strong>Prepared by:</strong>
            {{ $printedBy }}
        </div>

        <div>
            <strong>Total records:</strong>
            {{ number_format(
                $rows->count()
            ) }}
        </div>

        <div>
            <strong>Status filter:</strong>
            {{ $filters['status']
                ?: 'All permitted statuses' }}
        </div>
    </section>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Profile Number</th>
                <th>Child Laborer</th>
                <th>Age / Sex</th>
                <th>Address</th>
                <th>Guardian</th>
                <th>Education</th>
                <th>Employment</th>
                <th>Interventions</th>
                <th>Status</th>
                <th>Assigned Officer</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td class="nowrap">
                        {{ $index + 1 }}
                    </td>

                    <td class="nowrap">
                        {{ $row[
                            'profile_number'
                        ] }}
                    </td>

                    <td>
                        <strong>
                            {{ $row['full_name'] }}
                        </strong>

                        <br>

                        Born:
                        {{ $row['birth_date']
                            ?: 'Not recorded' }}
                    </td>

                    <td class="nowrap">
                        {{ $row['age'] !== null
                            ? $row['age'].' years'
                            : '—' }}

                        <br>

                        {{ $row['sex'] }}
                    </td>

                    <td>
                        {{ $row['address'] }}
                    </td>

                    <td>
                        {{ $row[
                            'guardian_name'
                        ] ?: 'Not recorded' }}

                        <br>

                        {{ $row[
                            'guardian_contact'
                        ] ?: 'No contact' }}
                    </td>

                    <td>
                        {{ $row[
                            'education_status'
                        ] ?: 'No current record' }}

                        <br>

                        {{ $row[
                            'grade_year_level'
                        ] ?: '—' }}
                    </td>

                    <td>
                        Working:
                        {{ $row[
                            'currently_working'
                        ] }}

                        <br>

                        {{ $row['occupation']
                            ?: 'No occupation' }}
                    </td>

                    <td class="nowrap">
                        {{ number_format(
                            $row[
                                'interventions_count'
                            ]
                        ) }}
                    </td>

                    <td class="nowrap">
                        {{ $row['status'] }}
                    </td>

                    <td>
                        {{ $row[
                            'assigned_officer'
                        ] ?: 'Not assigned' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="11"
                        style="text-align: center;
                               padding: 30px;"
                    >
                        No matching records.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">
        Generated by CLPMIS ·
        {{ $printedAt->format(
            'Y-m-d H:i:s'
        ) }}
    </p>
</body>
</html>