<x-dashboard-shell
    title="Comprehensive Profile Report"
    subtitle="{{ $childLaborer->profile_number }} — {{ $childLaborer->full_name }}"
    badge="{{ $childLaborer->status }}"
>
    <div class="flex flex-wrap justify-end gap-2">
        <a
            href="{{ route(
                'reports.child-laborers.index'
            ) }}"
            class="rounded-xl border
                   border-slate-300 px-5 py-3
                   text-sm font-bold text-slate-600"
        >
            Back to Reports
        </a>

        @can('print-reports')
            <a
                href="{{ route(
                    'reports.child-laborers.profile.print',
                    $childLaborer
                ) }}"
                target="_blank"
                class="rounded-xl bg-slate-800
                       px-5 py-3 text-sm font-bold
                       text-white"
            >
                Print Profile Report
            </a>
        @endcan
    </div>

    @unless ($includeSensitive)
        <div
            class="rounded-2xl border border-amber-200
                   bg-amber-50 p-4 text-sm
                   leading-6 text-amber-800"
        >
            This Viewer report excludes restricted health
            information, audit findings, audit recommendations,
            activity logs, and confidential documents.
        </div>
    @endunless

    <article class="profile-report-screen">
        <style>
            .profile-report-screen {
                display: grid;
                gap: 20px;
            }

            .report-header,
            .report-meta,
            .report-section {
                border: 1px solid #e2e8f0;
                border-radius: 22px;
                background: white;
                padding: 24px;
            }

            .report-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 24px;
            }

            .report-kicker {
                margin: 0;
                color: #0284c7;
                font-size: 12px;
                font-weight: 800;
                text-transform: uppercase;
            }

            .report-header h1 {
                margin: 8px 0 0;
                color: #0f172a;
                font-size: 28px;
            }

            .report-subtitle {
                margin: 7px 0 0;
                color: #64748b;
            }

            .profile-photo {
                width: 110px;
                height: 130px;
                border-radius: 15px;
                object-fit: cover;
            }

            .report-meta,
            .detail-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 14px;
            }

            .report-meta div,
            .detail-grid div {
                border-radius: 14px;
                background: #f8fafc;
                padding: 14px;
            }

            .detail-grid div.wide {
                grid-column: 1 / -1;
            }

            .detail-grid span {
                display: block;
                color: #94a3b8;
                font-size: 11px;
                font-weight: 800;
                text-transform: uppercase;
            }

            .detail-grid strong {
                display: block;
                margin-top: 5px;
                color: #334155;
                line-height: 1.5;
            }

            .report-section h2 {
                margin: 0 0 18px;
                color: #075985;
                font-size: 19px;
            }

            .report-section h3 {
                margin: 20px 0 10px;
                color: #334155;
                font-size: 15px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #cbd5e1;
                padding: 10px;
                vertical-align: top;
                text-align: left;
            }

            th {
                background: #e0f2fe;
                color: #075985;
                font-size: 11px;
                text-transform: uppercase;
            }

            td {
                color: #475569;
                font-size: 13px;
                line-height: 1.55;
            }

            .record-card {
                margin-top: 16px;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 16px;
            }

            .sensitive {
                border-color: #fbbf24;
                background: #fffbeb;
            }

            .empty {
                color: #64748b;
                font-size: 14px;
            }

            @media (max-width: 800px) {
                .report-meta,
                .detail-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        @include(
            'reports.child-laborers.partials.profile-content'
        )
    </article>
</x-dashboard-shell>