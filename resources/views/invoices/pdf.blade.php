<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Comercial — {{ $invoice->invoice_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        body {
            background-color: #ffffff;
            color: #1f2937;
            margin: 0;
            padding: 40px;
            font-size: 14px;
            line-height: 1.5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 24px;
            margin-bottom: 24px;
        }
        .logo-box h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.5px;
        }
        .logo-box p {
            margin: 4px 0 0 0;
            color: #6b7280;
            font-size: 12px;
        }
        .invoice-badge {
            text-align: right;
        }
        .invoice-badge h2 {
            margin: 0;
            font-size: 20px;
            color: #2563eb;
        }
        .invoice-badge p {
            margin: 4px 0 0 0;
            color: #4b5563;
            font-size: 13px;
        }
        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 6px;
        }
        .badge-issued { background-color: #fef3c7; color: #92400e; }
        .badge-paid { background-color: #d1fae5; color: #065f46; }
        .badge-cancelled { background-color: #fee2e2; color: #991b1b; }
        .grid-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 32px;
            gap: 20px;
        }
        .info-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            flex: 1;
        }
        .info-card h3 {
            margin: 0 0 8px 0;
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-card p {
            margin: 2px 0;
            font-size: 13px;
        }
        .info-card strong {
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
        }
        th {
            background-color: #f3f4f6;
            color: #374151;
            font-size: 12px;
            font-weight: 700;
            text-align: left;
            padding: 10px 12px;
            border-bottom: 2px solid #e5e7eb;
            text-transform: uppercase;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        tr:nth-child(even) td {
            background-color: #fafafa;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-box {
            margin-left: auto;
            width: 320px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
            color: #4b5563;
        }
        .totals-row.grand-total {
            border-top: 2px solid #e5e7eb;
            padding-top: 10px;
            margin-top: 8px;
            margin-bottom: 0;
            font-size: 16px;
            font-weight: 800;
            color: #111827;
        }
        .notes-box {
            margin-top: 32px;
            padding: 16px;
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 8px;
            font-size: 12px;
            color: #92400e;
        }
        .footer {
            margin-top: 48px;
            border-top: 1px solid #e5e7eb;
            padding-top: 16px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
            🖨️ Imprimir / Guardar como PDF
        </button>
    </div>

    <div class="header">
        <div class="logo-box">
            <h1>CARFLEET TRANSPORTATION S.A.S.</h1>
            <p>NIT: 900.123.456-7 · Transporte Corporativo y Especial</p>
            <p>Bogotá D.C., Colombia · operacion@carfleet.local</p>
        </div>
        <div class="invoice-badge">
            <h2>{{ $invoice->invoice_number }}</h2>
            <p>Fecha de Emisión: <strong>{{ $invoice->issue_date->format('d/m/Y') }}</strong></p>
            <div>
                <span class="badge-status badge-{{ $invoice->status->value }}">
                    {{ $invoice->status->label() }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid-info">
        <div class="info-card">
            <h3>Facturado a (Cliente / Solicitante)</h3>
            <p><strong>{{ $invoice->requester->name }}</strong></p>
            @if($invoice->requester->company_name)
                <p>Empresa: {{ $invoice->requester->company_name }}</p>
            @endif
            <p>Identificación: {{ $invoice->requester->formattedDocument() }}</p>
            @if($invoice->requester->phone)
                <p>Teléfono: {{ $invoice->requester->phone }}</p>
            @endif
            @if($invoice->requester->email)
                <p>Correo: {{ $invoice->requester->email }}</p>
            @endif
        </div>

        <div class="info-card">
            <h3>Resumen Operacional</h3>
            <p>Total Servicios Consolidados: <strong>{{ $invoice->trips->count() }} viajes</strong></p>
            <p>Distancia Acumulada: <strong>{{ number_format($invoice->trips->sum('distance_traveled'), 0, ',', '.') }} km</strong></p>
            <p>Modalidad de Facturación: <strong>Tarifa Estándar por Distancia / Base</strong></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Ruta (Origen &rarr; Destino)</th>
                <th>Vehículo / Placa</th>
                <th class="text-center">Fecha Salida</th>
                <th class="text-right">Distancia</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->trips as $trip)
                <tr>
                    <td><strong>{{ $trip->code }}</strong></td>
                    <td>{{ $trip->origin }} &rarr; {{ $trip->destination }}</td>
                    <td>{{ $trip->vehicle?->plate_number ?? 'N/A' }} ({{ $trip->vehicle?->brand }} {{ $trip->vehicle?->model }})</td>
                    <td class="text-center">{{ $trip->actual_departure_at?->format('d/m/Y H:i') ?? $trip->scheduled_departure_at->format('d/m/Y H:i') }}</td>
                    <td class="text-right">{{ number_format($trip->distance_traveled ?? 0, 0, ',', '.') }} km</td>
                    <td class="text-right"><strong>$ {{ number_format($trip->pivot->subtotal_amount, 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-box">
        <div class="totals-row">
            <span>Subtotal de Servicios:</span>
            <span>$ {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
        </div>
        <div class="totals-row">
            <span>Impuestos / Retenciones (0%):</span>
            <span>$ 0</span>
        </div>
        <div class="totals-row grand-total">
            <span>Total a Pagar:</span>
            <span>$ {{ number_format($invoice->total_amount, 0, ',', '.') }} COP</span>
        </div>
    </div>

    @if($invoice->notes)
        <div class="notes-box">
            <strong>Observaciones / Términos:</strong><br>
            {{ $invoice->notes }}
        </div>
    @endif

    <div class="footer">
        <p>Documento equivalente comercial para control y liquidación interna de flota corporativa — CarFleet Sistema de Gestión v1.0</p>
    </div>

</body>
</html>
