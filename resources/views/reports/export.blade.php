<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 28px 24px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 0 0 4px; }
        p { margin: 0 0 6px; }
        .note { color: #444; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 0; }
        th, td { border: 1px solid #bbb; padding: 2px 3px; vertical-align: top; word-wrap: break-word; }
        th { background: #f3f4f6; text-align: left; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>{{ $barangay }}</h1>
    <h2>{{ $title }}</h2>
    @if ($subtitle)
        <p>{{ $subtitle }}</p>
    @endif
    @if ($header)
        <p>{{ $header }}</p>
    @endif
    <p class="note">Generated {{ $generatedAt }}.</p>

    @if ($rows === [])
        <p>No records match this report.</p>
    @else
        <table>
            <thead>
                <tr>
                    @foreach ($headings as $heading)
                        <th>{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($row as $value)
                            <td>{{ $value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
