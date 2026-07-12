<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .kpi-bs { font-size: 13px; font-weight: bold; color: #1a2233; margin-top: 3px; }
        .kpi-usd { font-size: 10px; font-weight: bold; margin-top: 1px; }
        .kpi.local   .kpi-usd { color: #3498db; }
        .kpi.gravado .kpi-usd { color: #e67e22; }
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
        tbody td.num { text-align: right; }
        tbody td.local-usd   { color: #2980b9; font-weight: bold; }
        tbody td.gravado-usd { color: #d35400; font-weight: bold; }
        tbody td.total-usd   { color: #27ae60; font-weight: bold; }

        tfoot tr { background: #1e2a3a; color: #fff; }
        tfoot td { padding: 6px 8px; font-size: 9px; font-weight: bold; }
        tfoot td.num { text-align: right; }

        .footer { margin-top: 16px; border-top: 1px solid #d0dce8; padding-top: 6px; color: #888; font-size: 8px; }
    </style>
</head>
<body>

<div class="header">
    <h1>REPORTE DE VENTAS POR VENDEDOR — Local / Gravado</h1>
    <div class="subtitle">
        @if($config) {{ strtoupper($config->business_name ?? 'EMPRESA') }} @endif
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
            <div class="kpi-label">Ventas Locales (Sin IVA)</div>
            <div class="kpi-bs">Bs. {{ number_format($totals['local_bs'], 2) }}</div>
            <div class="kpi-usd">USD $ {{ number_format($totals['local_usd'], 2) }}</div>
        </div>
        <div class="kpi gravado">
            <div class="kpi-label">Ventas Gravadas (Con IVA)</div>
            <div class="kpi-bs">Bs. {{ number_format($totals['gravado_bs'], 2) }}</div>
            <div class="kpi-usd">USD $ {{ number_format($totals['gravado_usd'], 2) }}</div>
        </div>
        <div class="kpi total">
            <div class="kpi-label">Total General</div>
            <div class="kpi-bs">Bs. {{ number_format($totals['total_bs'], 2) }}</div>
            <div class="kpi-usd">USD $ {{ number_format($totals['total_usd'], 2) }}</div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="section-title">Detalle por Vendedor</div>

    <table>
        <thead>
            <tr>
                <th style="text-align:left; width:22%;">Vendedor</th>
                <th>Local (Bs.)</th>
                <th>Local (USD)</th>
                <th>Gravado (Bs.)</th>
                <th>Gravado (USD)</th>
                <th>Total (Bs.)</th>
                <th>Total (USD)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
                <tr>
                    <td>{{ $row->seller_name }}</td>
                    <td class="num">Bs. {{ number_format($row->local_bs, 2) }}</td>
                    <td class="num local-usd">$ {{ number_format($row->local_usd, 2) }}</td>
                    <td class="num">Bs. {{ number_format($row->gravado_bs, 2) }}</td>
                    <td class="num gravado-usd">$ {{ number_format($row->gravado_usd, 2) }}</td>
                    <td class="num">Bs. {{ number_format($row->total_bs, 2) }}</td>
                    <td class="num total-usd">$ {{ number_format($row->total_usd, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#999; padding: 12px;">
                        Sin datos para el período seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($reportData->count() > 0)
        <tfoot>
            <tr>
                <td>TOTALES</td>
                <td class="num">Bs. {{ number_format($totals['local_bs'], 2) }}</td>
                <td class="num" style="color:#7ecff7;">$ {{ number_format($totals['local_usd'], 2) }}</td>
                <td class="num">Bs. {{ number_format($totals['gravado_bs'], 2) }}</td>
                <td class="num" style="color:#f0b97a;">$ {{ number_format($totals['gravado_usd'], 2) }}</td>
                <td class="num">Bs. {{ number_format($totals['total_bs'], 2) }}</td>
                <td class="num" style="color:#7ef0b4;">$ {{ number_format($totals['total_usd'], 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Este reporte fue generado automáticamente por JSPOS · Sistema de Gestión Comercial.
        @if($config) {{ $config->business_name ?? '' }} @endif
    </div>
</div>

</body>
</html>
