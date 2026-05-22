<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $reportTitle }} Print Report</title>
    <style>
        body {
            margin: 28px;
            color: #0f172a;
            font-family: Arial, sans-serif;
            background: #ffffff;
        }
        .print-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 18px;
        }
        .print-actions button {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: 700;
            cursor: pointer;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 26px;
            line-height: 1.2;
        }
        .meta {
            margin-bottom: 20px;
            color: #475569;
            font-size: 13px;
            line-height: 1.7;
        }
        .scope {
            color: #2563eb;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            font-size: 11px;
        }
        th {
            background: #dbeafe;
            color: #0f172a;
            font-weight: 800;
            white-space: nowrap;
        }
        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 7px 9px;
            text-align: left;
            vertical-align: top;
        }
        td {
            white-space: nowrap;
        }
        td.long-cell {
            white-space: normal;
            min-width: 170px;
        }
        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        @media print {
            body {
                margin: 12mm;
            }
            .print-actions {
                display: none;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" onclick="window.close()">Close</button>
    </div>

    <h1>{{ $reportTitle }}</h1>
    <div class="meta">
        <div>Center ID: <strong>{{ $centerId }}</strong> | Period: <strong>{{ $periodLabel }}</strong> | Total Records: <strong>{{ $reportTotal }}</strong></div>
        <div class="scope">{{ $scopeLabel }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                @foreach($reportColumns as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($reportRows as $index => $reportRow)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @foreach($reportColumns as $column => $label)
                        @php
                            $value = data_get($reportRow, $column, 'N/A');
                            $value = filled($value) ? $value : 'N/A';
                        @endphp
                        <td @class(['long-cell' => mb_strlen((string) $value) > 35])>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 1 + count($reportColumns) }}">No report data found for the selected category and period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', () => {
            window.focus();
            window.print();
        });
    </script>
</body>
</html>
