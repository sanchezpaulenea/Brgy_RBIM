<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $barangay }} database records</title>
    <style>
        @page { margin: 28px 24px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 16px 0 4px; }
        p { margin: 0 0 6px; }
        .note { color: #444; }
        table { width: 100%; border-collapse: collapse; margin: 0 0 8px; page-break-inside: auto; }
        th, td { border: 1px solid #bbb; padding: 2px 3px; vertical-align: top; word-wrap: break-word; }
        th { background: #f3f4f6; text-align: left; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>{{ $barangay }}</h1>
    <p>Database records for printing. Generated {{ $generatedAt }}.</p>
    <p class="note">This copy is for reading and filing. Restore the system from the SQL backup, not from this PDF. Password hashes are omitted. Tables longer than the print limit are shortened; the Excel and SQL copies include every row.</p>

    @forelse ($tables as $table)
        <h2>{{ $table['name'] }} ({{ $table['total'] }} {{ $table['total'] === 1 ? 'record' : 'records' }})</h2>
        @if ($table['truncated'])
            <p class="note">Showing the first {{ count($table['rows']) }} records.</p>
        @endif
        @if ($table['columns'] === [])
            <p class="note">No printable columns.</p>
        @elseif ($table['rows'] === [])
            <p class="note">No records.</p>
        @else
            <table>
                <thead>
                    <tr>
                        @foreach ($table['columns'] as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($table['rows'] as $row)
                        <tr>
                            @foreach ($row as $value)
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @empty
        <p>No records were available to export.</p>
    @endforelse
</body>
</html>
