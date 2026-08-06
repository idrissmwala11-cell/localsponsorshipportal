<x-app-layout>
    <x-slot name="header">Church Reports</x-slot>

    @once
        <style>
            .reports-shell {
                max-width: 96rem;
                margin: 0 auto;
            }
            .reports-card {
                overflow: hidden;
                border-radius: 2rem;
                border: 1px solid rgba(191, 219, 254, 0.85);
                background:
                    linear-gradient(180deg, rgba(255, 255, 255, 0.985), rgba(247, 250, 255, 0.96)),
                    radial-gradient(circle at top right, rgba(191, 219, 254, 0.18), transparent 22rem);
                box-shadow: 0 32px 80px -48px rgba(15, 23, 42, 0.26);
                backdrop-filter: blur(14px);
            }
            .reports-hero {
                position: relative;
                padding: 1.65rem 1.75rem 1.35rem;
                background:
                    radial-gradient(circle at top left, rgba(96, 165, 250, 0.16), transparent 19rem),
                    linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(243, 248, 255, 0.95));
            }
            .reports-hero-content {
                position: relative;
                z-index: 1;
            }
            .reports-stat-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 0.85rem;
                margin-top: 1.25rem;
            }
            .reports-stat-card {
                min-height: 5.3rem;
                border-radius: 1rem;
                border: 1px solid rgba(191, 219, 254, 0.9);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(248, 250, 252, 0.9));
                padding: 0.9rem 1rem;
                box-shadow: 0 18px 30px -28px rgba(37, 99, 235, 0.28);
            }
            .reports-stat-card span {
                color: #64748b;
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }
            .reports-stat-card strong {
                display: block;
                margin-top: 0.45rem;
                color: #0f172a;
                font-size: 1.45rem;
                font-weight: 900;
                line-height: 1;
            }
            .reports-hero::after {
                content: '';
                position: absolute;
                inset: auto -3rem -4.5rem auto;
                width: 14rem;
                height: 14rem;
                border-radius: 999px;
                background: radial-gradient(circle, rgba(147, 197, 253, 0.34), transparent 68%);
                filter: blur(18px);
                pointer-events: none;
            }
            .reports-kicker {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                padding: 0.42rem 0.8rem;
                border-radius: 999px;
                background: linear-gradient(135deg, #eff6ff, #dbeafe);
                border: 1px solid #bfdbfe;
                color: #2563eb;
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.14em;
                text-transform: uppercase;
            }
            .reports-kicker::before {
                content: '';
                width: 0.42rem;
                height: 0.42rem;
                border-radius: 999px;
                background: #2563eb;
            }
            .reports-filter-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.3fr) minmax(0, 0.8fr) minmax(0, 0.9fr) auto auto;
                gap: 1.05rem;
                align-items: end;
            }
            .reports-filter-panel {
                padding: 1.55rem 1.65rem;
                border-top: 1px solid rgba(226, 232, 240, 0.8);
                border-bottom: 1px solid rgba(226, 232, 240, 0.8);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.78), rgba(248, 250, 252, 0.72));
            }
            .reports-summary {
                display: flex;
                flex-wrap: wrap;
                gap: 0.85rem;
                margin-top: 1.15rem;
            }
            .reports-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                padding: 0.62rem 0.96rem;
                border-radius: 999px;
                background: linear-gradient(180deg, #ffffff, #f8fbff);
                border: 1px solid #dbeafe;
                color: #334155;
                font-size: 0.8rem;
                font-weight: 700;
                box-shadow: 0 12px 24px -22px rgba(37, 99, 235, 0.28);
            }
            .reports-pill strong {
                color: #0f172a;
            }
            .reports-body {
                padding: 1.7rem;
            }
            .reports-section-title {
                font-size: clamp(1.55rem, 2vw, 2rem);
                line-height: 1.1;
                color: #0f172a;
            }
            .reports-table-shell {
                overflow: hidden;
                border-radius: 1.5rem;
                border: 1px solid rgba(219, 234, 254, 0.95);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 252, 255, 0.98));
                box-shadow: 0 20px 36px -34px rgba(15, 23, 42, 0.22);
            }
            .reports-table-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 1.05rem 1.2rem;
                border-bottom: 1px solid rgba(226, 232, 240, 0.9);
                background: linear-gradient(180deg, #f8fbff, #eef5ff);
            }
            .reports-table-note {
                color: #64748b;
                font-size: 0.82rem;
            }
            .reports-table-wrap {
                overflow-x: auto;
            }
            .reports-table-wrap table tbody tr:hover {
                background: rgba(239, 246, 255, 0.62);
            }
            .reports-user-link {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                color: #1d4ed8;
                font-weight: 900;
                text-decoration: none;
            }
            .reports-user-link:hover {
                color: #0f5eb8;
                text-decoration: underline;
            }
            .reports-table-wrap table thead th {
                position: sticky;
                top: 0;
                background: #f8fbff;
                z-index: 1;
            }
            .reports-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
                justify-content: flex-end;
            }
            .print-report-sheet {
                display: none;
            }
            @media (max-width: 1280px) {
                .reports-filter-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
                .reports-stat-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
            @media (max-width: 768px) {
                .reports-shell {
                    max-width: 100%;
                }
                .reports-hero,
                .reports-filter-panel,
                .reports-body {
                    padding-left: 1rem;
                    padding-right: 1rem;
                }
                .reports-filter-grid {
                    grid-template-columns: 1fr;
                }
                .reports-actions {
                    justify-content: stretch;
                }
                .reports-actions > * {
                    flex: 1 1 100%;
                    justify-content: center;
                }
                .reports-stat-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endonce

    <div class="workspace-page">
        <div class="workspace-container">
            <div class="reports-shell">
                <div class="reports-card">
                    <div class="reports-hero">
                        <div class="reports-hero-content">
                            <span class="reports-kicker">Reporting Workspace</span>
                            <h1 class="mt-4 text-3xl lg:text-4xl font-black text-slate-900">Church Reports</h1>
                            <p class="mt-3 max-w-4xl text-sm leading-7 text-slate-600">
                                {{ auth()->user()->isOfficialAdmin()
                                    ? 'Run admin and user reports by report type, center ID, and time period. Click a user name to open that user profile and center participant summary.'
                                    : 'Run participant section reports by category, center ID, and time period. Records are shared by center ID for users in the same center.' }}
                            </p>
                            <p class="mt-3 text-sm font-medium text-blue-700">{{ $scopeLabel }}</p>

                            <div class="reports-stat-grid">
                                <div class="reports-stat-card">
                                    <span>Participants</span>
                                    <strong>{{ $participantsCount }}</strong>
                                </div>
                                <div class="reports-stat-card">
                                    <span>Users</span>
                                    <strong>{{ $usersCount }}</strong>
                                </div>
                                <div class="reports-stat-card">
                                    <span>Sponsorships</span>
                                    <strong>{{ $sponsorshipsCount }}</strong>
                                </div>
                                <div class="reports-stat-card">
                                    <span>Notifications</span>
                                    <strong>{{ $notificationsCount }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="reports-filter-panel">
                        <form method="GET" action="{{ route('reports.index') }}" class="reports-filter-grid">
                            <div>
                                <label class="workspace-field-label">Select Category</label>
                                <select name="module" class="workspace-select px-4 py-3">
                                    @foreach($moduleDefinitions as $moduleKey => $definition)
                                        <option value="{{ $moduleKey }}" @selected($selectedModule === $moduleKey)>{{ $definition['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="workspace-field-label">Center ID</label>
                                <select name="center_id" class="workspace-select px-4 py-3">
                                    <option value="all" @selected($selectedCenterId === 'all')>{{ auth()->user()->isOfficialAdmin() ? 'All Centers' : 'All Managed Centers' }}</option>
                                    @foreach($centerOptions as $centerOption)
                                        <option value="{{ $centerOption['value'] }}" @selected($selectedCenterId === $centerOption['value'])>{{ $centerOption['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="workspace-field-label">Report Period</label>
                                <select name="period" class="workspace-select px-4 py-3">
                                    @foreach($periodOptions as $periodKey => $periodLabel)
                                        <option value="{{ $periodKey }}" @selected($selectedPeriod === $periodKey)>{{ $periodLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn-primary justify-center px-6">Run Report</button>
                            <a
                                href="{{ route('reports.print', ['module' => $selectedModule, 'period' => $selectedPeriod, 'center_id' => $selectedCenterId]) }}"
                                target="_blank"
                                class="btn-ghost justify-center px-6">
                                Print Report
                            </a>
                        </form>

                        <div class="reports-summary">
                            <span class="reports-pill">Center ID: <strong>{{ $centerId }}</strong></span>
                            <span class="reports-pill">Period: <strong>{{ $periodOptions[$selectedPeriod] }}</strong></span>
                            <span class="reports-pill">Total Records: <strong>{{ $reportTotal }}</strong></span>
                            <span class="reports-pill">Category: <strong>{{ $reportModule['label'] }}</strong></span>
                        </div>
                    </div>

                    <div class="reports-body space-y-5">
                        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                            <div>
                                <h2 class="reports-section-title font-black">{{ $reportModule['label'] }}</h2>
                                <p class="mt-2 text-sm text-slate-500">Output for the selected report category.</p>
                            </div>

                            <div class="reports-actions">
                                <a href="{{ route('reports.export', ['type' => 'report', 'module' => $selectedModule, 'period' => $selectedPeriod, 'center_id' => $selectedCenterId]) }}" class="btn-primary">Export This Report Excel</a>
                                @unless(auth()->user()->isOfficialAdmin())
                                    <a href="{{ route('reports.export', ['type' => 'all-items', 'center_id' => $selectedCenterId]) }}" class="btn-ghost">Export All Items Excel</a>
                                    <a href="{{ route('reports.export', ['type' => 'sponsorships', 'center_id' => $selectedCenterId]) }}" class="btn-ghost">Export Sponsorships Excel</a>
                                @endunless
                            </div>
                        </div>

                        <div class="reports-table-shell">
                            <div class="reports-table-head">
                                <div>
                                    <p class="workspace-label">Report Output</p>
                                    <p class="reports-table-note mt-1">{{ $reportTableNote }}</p>
                                </div>
                            </div>

                            <div class="reports-table-wrap">
                                <table id="report-output-table" class="w-full modern-table">
                                    <thead>
                                        <tr>
                                            <th class="text-left">#</th>
                                            @foreach($reportColumns as $label)
                                                <th class="text-left">{{ $label }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reportRows as $index => $reportRow)
                                            <tr>
                                                <td>{{ $reportRows->firstItem() + $index }}</td>
                                                @foreach($reportColumns as $column => $label)
                                                    @php
                                                        $value = data_get($reportRow, $column, 'N/A');
                                                        $cellClass = in_array($column, ['account_name', 'project_name', 'topic', 'instructor_name'], true)
                                                            ? 'table-primary'
                                                            : 'table-secondary';
                                                        $extraClass = in_array($column, ['local_participant_id', 'participant_id', 'center_id'], true)
                                                            ? ' whitespace-nowrap font-mono text-xs'
                                                            : '';
                                                    @endphp
                                                    <td class="{{ $cellClass . $extraClass }}">
                                                        @if(($reportModule['type'] ?? null) === 'accounts' && $column === 'name' && filled(data_get($reportRow, '_account_url')))
                                                            <a href="{{ data_get($reportRow, '_account_url') }}" class="reports-user-link">
                                                                <i class="bi bi-person-lines-fill"></i>
                                                                <span>{{ filled($value) ? $value : 'N/A' }}</span>
                                                            </a>
                                                        @else
                                                            {{ filled($value) ? $value : 'N/A' }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ 1 + count($reportColumns) }}" class="text-center py-14 text-sm text-slate-500">
                                                    No report data found for the selected category and period.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{ $reportRows->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function printReportTable() {
            const table = document.getElementById('report-output-table');

            if (!table) {
                window.print();
                return;
            }

            const title = @json($reportModule['label']);
            const centerId = @json($centerId);
            const period = @json($periodOptions[$selectedPeriod]);
            const totalRecords = @json($reportTotal);
            const scopeLabel = @json($scopeLabel);

            const printWindow = window.open('', '_blank', 'width=1200,height=800');

            if (!printWindow) {
                window.print();
                return;
            }

            printWindow.document.write(`
                <html>
                    <head>
                        <title>${title} Report</title>
                        <style>
                            body {
                                font-family: Inter, Arial, sans-serif;
                                margin: 32px;
                                color: #0f172a;
                            }
                            h1 {
                                margin: 0 0 10px;
                                font-size: 28px;
                            }
                            .meta {
                                margin-bottom: 22px;
                                color: #475569;
                                font-size: 14px;
                                line-height: 1.6;
                            }
                            .scope {
                                margin-top: 6px;
                                color: #2563eb;
                                font-weight: 600;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                font-size: 12px;
                            }
                            thead th {
                                background: #0f172a;
                                color: #ffffff;
                                text-align: left;
                                padding: 10px 12px;
                                border: 1px solid #cbd5e1;
                            }
                            tbody td {
                                padding: 10px 12px;
                                border: 1px solid #cbd5e1;
                                vertical-align: top;
                            }
                            tbody tr:nth-child(even) {
                                background: #f8fafc;
                            }
                        </style>
                    </head>
                    <body>
                        <h1>${title}</h1>
                        <div class="meta">
                            <div>Center ID: <strong>${centerId}</strong> | Period: <strong>${period}</strong> | Total Records: <strong>${totalRecords}</strong></div>
                            <div class="scope">${scopeLabel}</div>
                        </div>
                        ${table.outerHTML}
                    </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
    </script>
</x-app-layout>
