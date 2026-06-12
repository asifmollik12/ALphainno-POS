<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Supplier List — {{ $setting->company_name ?? 'Alphainno POS' }}</title>
    <style>
        body { font-family: system-ui, sans-serif; font-size: 12px; color: #1e293b; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #64748b; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 10px; text-align: left; }
        th { background: #0c1222; color: #fff; font-size: 11px; text-transform: uppercase; }
        tr:nth-child(even) { background: #f8fafc; }
    </style>
</head>
<body onload="window.print()">
    <h1>{{ $setting->company_name ?? 'Alphainno POS' }}</h1>
    <p class="meta">Supplier List — {{ now()->format('M d, Y') }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suppliers as $s)
            <tr>
                <td>{{ $s->id }}</td>
                <td>{{ $s->name }}</td>
                <td>{{ $s->phone }}</td>
                <td>{{ $s->email }}</td>
                <td>{{ $s->address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
