<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dnevni izvjestaj</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <h2 style="margin-bottom: 6px;">Dnevni izvjestaj je podnesen</h2>
    <p style="margin-top: 0;">
        Lokacija: <strong>{{ $summary['location_name'] }}</strong><br>
        Datum: <strong>{{ \Illuminate\Support\Carbon::parse($summary['report_date'])->format('d.m.Y') }}</strong><br>
        Status: <strong>{{ strtoupper($summary['status']) }}</strong>
    </p>

    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; border-color: #e5e7eb;">
        <tr><td>Pregledi ukupno</td><td><strong>{{ $summary['services_count'] }}</strong></td></tr>
        <tr><td>Saradnici ukupno</td><td><strong>{{ $summary['collaborators_total_count'] ?? 0 }}</strong></td></tr>
        @foreach (($summary['by_collaborator'] ?? []) as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td><strong>{{ $row['count'] }}</strong></td>
            </tr>
        @endforeach
        <tr><td>Glavni doktori ukupno</td><td><strong>{{ $summary['lead_doctors_total_count'] ?? 0 }}</strong></td></tr>
        @foreach (($summary['by_lead_doctor'] ?? []) as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td><strong>{{ $row['count'] }}</strong></td>
            </tr>
        @endforeach
        <tr><td>Znacajni pregledi</td><td><strong>{{ $summary['significant_services_total_count'] ?? 0 }}</strong></td></tr>
        @foreach (($summary['by_significant_service'] ?? []) as $row)
            <tr>
                <td>{{ strtoupper($row['name']) }}</td>
                <td><strong>{{ $row['count'] }}</strong></td>
            </tr>
        @endforeach
        @if (($summary['include_new_patients'] ?? true))
            <tr><td>Novi pacijenti</td><td><strong>{{ $summary['new_patients_count'] ?? 0 }}</strong></td></tr>
        @endif
        <tr><td>Broj usluga</td><td><strong>{{ $summary['services_count'] }}</strong></td></tr>
        <tr><td>Promet usluga</td><td><strong>{{ number_format($summary['services_amount'], 2, ',', '.') }} KM</strong></td></tr>
        <tr><td>Naplaceno</td><td><strong>{{ number_format($summary['paid_amount'], 2, ',', '.') }} KM</strong></td></tr>
        <tr><td>Dugovanje</td><td><strong>{{ number_format($summary['remaining_amount'], 2, ',', '.') }} KM</strong></td></tr>
        <tr><td>Neplacene stavke</td><td><strong>{{ $summary['unpaid_items_count'] }}</strong></td></tr>
        <tr><td>Djelimicno placene stavke</td><td><strong>{{ $summary['partial_items_count'] }}</strong></td></tr>
        <tr><td>Broj nalaza</td><td><strong>{{ $summary['findings_count'] }}</strong></td></tr>
        <tr><td>Vrijednost nalaza</td><td><strong>{{ number_format($summary['findings_amount'], 2, ',', '.') }} KM</strong></td></tr>
        <tr><td>Ukupan promet</td><td><strong>{{ number_format($summary['grand_total'], 2, ',', '.') }} KM</strong></td></tr>
    </table>

    <p style="margin-top: 16px;">
        Podnosilac: <strong>{{ $dailyReport->submittedBy?->name ?? '-' }}</strong><br>
        Kreirao: <strong>{{ $dailyReport->createdBy?->name ?? '-' }}</strong>
    </p>
</body>
</html>
