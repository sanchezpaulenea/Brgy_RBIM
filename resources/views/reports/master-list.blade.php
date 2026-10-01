<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 0 0 12px; font-weight: normal; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; vertical-align: top; text-align: left; white-space: pre-line; }
        th { background: #e8eee9; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    @if ($subtitle)
        <h2>{{ $subtitle }}</h2>
    @endif
    @if (! empty($header))
        <p>{{ $header }}</p>
    @endif
    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(count($headings), 1) }}">No records match this report.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
