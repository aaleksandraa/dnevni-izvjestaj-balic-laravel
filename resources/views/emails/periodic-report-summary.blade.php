<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Periodicni izvjestaj</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <h2 style="margin-bottom: 6px;">
        {{ $reportType === 'weekly' ? 'Sedmicni' : 'Mjesecni' }} izvjestaj
    </h2>
    <p style="margin-top: 0;">
        Period: <strong>{{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}</strong>
    </p>

    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; border-color: #e5e7eb;">
        <tr><td>Broj izvjestaja</td><td><strong>{{ $summary['reports_count'] }}</strong></td></tr>
        <tr><td>Broj usluga</td><td><strong>{{ $summary['services_count'] }}</strong></td></tr>
        <tr><td>Promet usluga</td><td><strong>{{ number_format($summary['services_amount'], 2, ',', '.') }} KM</strong></td></tr>
        <tr><td>Naplaceno</td><td><strong>{{ number_format($summary['paid_amount'], 2, ',', '.') }} KM</strong></td></tr>
        <tr><td>Dugovanje</td><td><strong>{{ number_format($summary['remaining_amount'], 2, ',', '.') }} KM</strong></td></tr>
        <tr><td>Neplacene stavke</td><td><strong>{{ $summary['unpaid_items_count'] }}</strong></td></tr>
        <tr><td>Djelimicno placene stavke</td><td><strong>{{ $summary['partial_items_count'] }}</strong></td></tr>
        <tr><td>Broj nalaza</td><td><strong>{{ $summary['findings_count'] }}</strong></td></tr>
        <tr><td>Vrijednost nalaza</td><td><strong>{{ number_format($summary['findings_amount'], 2, ',', '.') }} KM</strong></td></tr>
        <tr><td>Ukupno</td><td><strong>{{ number_format($summary['grand_total'], 2, ',', '.') }} KM</strong></td></tr>
    </table>

    @if (!empty($summary['by_location']))
        <h3 style="margin-top: 20px;">Pregled po lokacijama</h3>
        <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; border-color: #e5e7eb;">
            <thead>
                <tr>
                    <th>Lokacija</th>
                    <th>Izvjestaji</th>
                    <th>Usluge</th>
                    <th>Naplaceno</th>
                    <th>Dug</th>
                    <th>Ukupno</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($summary['by_location'] as $locationRow)
                    <tr>
                        <td>{{ $locationRow['location_name'] }}</td>
                        <td>{{ $locationRow['reports_count'] }}</td>
                        <td>{{ $locationRow['services_count'] }}</td>
                        <td>{{ number_format($locationRow['paid_amount'], 2, ',', '.') }} KM</td>
                        <td>{{ number_format($locationRow['remaining_amount'], 2, ',', '.') }} KM</td>
                        <td>{{ number_format($locationRow['grand_total'], 2, ',', '.') }} KM</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
