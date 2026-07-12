<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Ventas por Vendedor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #222; background: #fff; }

        .header { padding: 12px 20px; background: #1e2a3a; color: #fff; border-bottom: 3px solid #3498db; }
        .header h1 { font-size: 16px; font-weight: bold; letter-spacing: 1px; }
        .header .subtitle { font-size: 10px; color: #a0b4c8; margin-top: 3px; }
        .header .meta { font-size: 9px; color: #7a9ab8; margin-top: 2px; }

        .content { padding: 12px 20px; }

        /* KPIs */
        .kpis { display: table; width: 100%; margin-bottom: 14px; border-collapse: separate; border-spacing: 8px; }
        .kpi { display: table-cell; width: 33%; background: #f4f7fb; border: 1px solid #d0dce8; border-radius: 4px; padding: 8px 10px; vertical-align: top; }
        .kpi.local   { border-top: 3px solid #3498db; }
        .kpi.gravado { border-top: 3px solid #e67e22; }
        .kpi.total   { border-top: 3px solid #27ae60; }
        .kpi-label { font-size: 8px; text-transform: uppercase; color: #666; font-weight: bold; letter-spacing: 0.5px; }
        .kpi-sub { font-size: 7px; color: #999; margin-top: 1px; }
        .kpi-usd { font-size: 15px; font-weight: bold; margin-top: 4px; }
        .kpi.local   .kpi-usd { color: #2980b9; }
        .kpi.gravado .kpi-usd { color: #d35400; }
        .kpi.total   .kpi-usd { color: #27ae60; }

        /* Table */
        .section-title { font-size: 11px; font-weight: bold; color: #1e2a3a; border-bottom: 2px solid #3498db; padding-bottom: 4px; margin-bottom: 8px; }

        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #1e2a3a; color: #fff; }
        thead th { padding: 6px 8px; text-align: center; font-size: 9px; font-weight: bold; letter-spacing: 0.3px; }
        thead th:first-child { text-align: left; }

        tbody tr:nth-child(even) { background: #f4f7fb; }
        tbody tr:nth-child(odd)  { background: #fff; }
        tbody td { padding: 5px 8px; font-size: 9px; border-bottom: 1px solid #e0e8f0; }
        tbody td:first-child { font-weight: bold; color: #1a2233; }
        tbody td.num  { text-align: right; }
        tbody td.cnt  { text-align: center; }
        tbody td.local   { color: #2980b9; font-weight: bold; }
        tbody td.gravado { color: #d35400; font-weight: bold; }
        tbody td.total   { color: #27ae60; font-weight: bold; }

        tfoot tr { background: #1e2a3a; color: #fff; }
        tfoot td { padding: 6px 8px; font-size: 9px; font-weight: bold; }
        tfoot td.num { text-align: right; }
        tfoot td.cnt { text-align: center; }

        .note { margin-top: 10px; padding: 6px 10px; background: #f0f4f8; border-left: 3px solid #7a9ab8; font-size: 8px; color: #555; }
        .footer { margin-top: 14px; border-top: 1px solid #d0dce8; padding-top: 6px; color: #888; font-size: 8px; }
    </style>
</head>
<body>

<div class="header">
    <h1>REPORTE DE VENTAS POR VENDEDOR</h1>
    <div class="subtitle">
        Clasificación Local / Gravado — Montos en USD
        @if($config) &nbsp;|&nbsp; {{ strtoupper($config->business_name ?? '') }} @endif
    </div>
    <div class="meta">
        Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
        @if($dateFrom !== $dateTo) — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }} @endif
        &nbsp;|&nbsp; Generado: {{ $generatedAt }}
    </div>
</div>

<div class="content">

    {{-- KPIs --}}
    <div class="kpis">
        <div class="kpi local">
            <div class="kpi-label">Ventas Locales</div>
            <div class="kpi-sub">Departamentos tipo LOCAL</div>
            <div class="kpi-usd">USD $ {{ number_format($totals['local_usd'], 2) }}</div>
        </div>
        <div class="kpi gravado">
            <div class="kpi-label">Ventas Gravadas</div>
            <div class="kpi-sub">Departamentos tipo GRAVADO</div>
            <div class="kpi-usd">USD $ {{ number_format($totals['gravado_usd'], 2) }}</div>
        </div>
        <div class="kpi total">
            <div class="kpi-label">Total General</div>
            <div class="kpi-sub">Local + Gravado</div>
            <div class="kpi-usd">USD $ {{ number_format($totals['total_usd'], 2) }}</div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="section-title">Detalle por Vendedor</div>

    <table>
        <thead>
            <tr>
                <th style="text-align:left; width:30%;">Vendedor</th>
                <th>Local (USD)</th>
                <th>Gravado (USD)</th>
                <th>Total (USD)</th>
                <th style="width:8%;"># Ventas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
                <tr>
                    <td>{{ $row->seller_name }}</td>
                    <td class="num local">$ {{ number_format($row->local_usd, 2) }}</td>
                    <td class="num gravado">$ {{ number_format($row->gravado_usd, 2) }}</td>
                    <td class="num total">$ {{ number_format($row->total_usd, 2) }}</td>
                    <td class="cnt">{{ $row->sale_count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#999; padding:12px;">
                        Sin datos para el período seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($reportData->count() > 0)
        <tfoot>
            <tr>
                <td>TOTALES</td>
                <td class="num" style="color:#7ecff7;">$ {{ number_format($totals['local_usd'], 2) }}</td>
                <td class="num" style="color:#f0b97a;">$ {{ number_format($totals['gravado_usd'], 2) }}</td>
                <td class="num" style="color:#7ef0b4;">$ {{ number_format($totals['total_usd'], 2) }}</td>
                <td class="cnt">{{ $reportData->sum('sale_count') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="note">
        <b>Nota:</b> "Local" y "Gravado" son clasificaciones del departamento al que pertenece la categoría del producto.
        No representan el tipo de pago ni la aplicación de IVA.
    </div>

    <div class="footer">
        Este reporte fue generado automáticamente por JSPOS.
        @if($config) {{ $config->business_name ?? '' }} @endif
    </div>
</div>

</body>
</html>
