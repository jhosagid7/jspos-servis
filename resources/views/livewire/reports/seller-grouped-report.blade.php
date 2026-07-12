<div>
    <div class="row">
        <!-- Sidebar - Opciones de Consulta -->
        <div class="col-sm-12 col-md-3">
            <div class="card mb-3">
                <div class="p-1 card-header bg-dark">
                    <h5 class="text-center txt-light mb-0">Filtros de Reporte</h5>
                </div>

                <div class="card-body">
                    <!-- Selector de Operadores -->
                    <div class="mt-2">
                        <span class="f-14"><b>Filtrar Operadores / Cajeros</b></span>
                        <div class="border p-2 rounded mt-1" style="max-height: 180px; overflow-y: auto; background-color: #f8f9fa;">
                            @forelse ($operatorsList as $operator)
                                <div class="custom-control custom-checkbox mb-1">
                                    <input type="checkbox" class="custom-control-input" id="operator_{{ $operator->id }}" value="{{ $operator->id }}" wire:model.live="selectedOperators">
                                    <label class="custom-control-label f-12" for="operator_{{ $operator->id }}">{{ $operator->name }}</label>
                                </div>
                            @empty
                                <div class="text-center text-muted f-12 py-2">No se encontraron operadores</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Rango de Fechas -->
                    <div class="mt-3">
                        <span class="f-14"><b>Desde</b></span>
                        <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm mt-1">
                    </div>
                    <div class="mt-2">
                        <span class="f-14"><b>Hasta</b></span>
                        <input type="date" wire:model.live="dateTo" class="form-control form-control-sm mt-1">
                    </div>

                    <!-- Botón Hoy -->
                    <div class="mt-2">
                        <button wire:click.prevent="setToday" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fa fa-calendar-day me-1"></i> Hoy
                        </button>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="mt-3">
                        <button wire:key="btn-seller-grouped-search" wire:click.prevent="searchData" class="btn btn-dark w-100">
                            <i class="fa fa-sync"></i> Generar Reporte
                        </button>
                    </div>

                    <!-- Botones PDF (solo visible cuando hay datos) -->
                    @if($showReport)
                    <div class="mt-2 d-flex gap-1">
                        <button wire:click.prevent="openPdfPreview" class="btn btn-outline-danger btn-sm flex-fill" title="Previsualizar PDF">
                            <i class="fa fa-eye"></i> Vista Previa
                        </button>
                        <button wire:click.prevent="generatePdf" class="btn btn-danger btn-sm flex-fill" title="Descargar PDF">
                            <i class="fa fa-file-pdf"></i> PDF
                        </button>
                    </div>
                    @endif

                    <!-- Leyenda -->
                    <div class="mt-3 p-2 rounded" style="background:#f8f9fa; border-left: 3px solid #6c757d;">
                        <p class="f-11 text-muted mb-1"><b>¿Qué es LOCAL / GRAVADO?</b></p>
                        <p class="f-11 text-muted mb-0">Es la <b>clasificación del departamento</b> al que pertenece el producto, no el tipo de pago ni si cobró IVA.</p>
                        <p class="f-11 text-muted mb-0 mt-1">Se configura en <b>Registros Maestros → Categorías</b>.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de Resultados -->
        <div class="col-sm-12 col-md-9">
            <div class="card card-absolute">
                <div class="card-header bg-dark d-flex justify-content-between align-items-center">
                    <h5 class="txt-light mb-0">Cobranza por Operador / Usuario</h5>
                    @if($showReport && $dateFrom)
                        <span class="badge badge-light f-12">
                            {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                            @if($dateFrom !== $dateTo)
                                — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                            @endif
                        </span>
                    @endif
                </div>

                <div class="card-body">
                    <!-- Mensaje de instrucción -->
                    <div class="alert alert-info text-center {{ !$showReport ? '' : 'd-none' }}">
                        <i class="fa fa-info-circle me-2"></i>
                        Selecciona los filtros en la barra lateral y haz clic en <strong>Generar Reporte</strong>.<br>
                        Usa el botón <strong>Hoy</strong> para ver rápidamente el reporte del día.
                    </div>

                    <!-- Panel de Resultados -->
                    <div class="{{ !$showReport ? 'd-none' : '' }}">

                        <!-- KPIs de Resumen -->
                        <h5 class="txt-primary mb-3"><i class="fa fa-info-circle"></i> Totales Cobrados en USD</h5>
                        
                        <div class="row">
                            <!-- Metodos individuales -->
                            @foreach($totalsByMethod as $method)
                                <div class="col-md-3 mb-3">
                                    <div class="card shadow-sm border-left border-info h-100">
                                        <div class="card-body p-3">
                                            <div class="f-12 text-muted uppercase font-weight-bold">
                                                {{ strtoupper($method['method']) }} ({{ strtoupper($method['currency']) }})
                                            </div>
                                            <div class="f-11 text-muted">Original: {{ number_format($method['total_amount'], 2) }} {{ strtoupper($method['currency']) }}</div>
                                            <div class="f-18 font-weight-bold text-info mt-2">
                                                USD ${{ number_format($method['total_usd'], 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <!-- Total General -->
                            <div class="col-md-3 mb-3">
                                <div class="card shadow-sm border-left border-success h-100 bg-light">
                                    <div class="card-body p-3">
                                        <div class="f-12 text-dark uppercase font-weight-bold">TOTAL GENERAL</div>
                                        <div class="f-11 text-muted">Suma total equivalente</div>
                                        <div class="f-18 font-weight-bold text-success mt-2">
                                            USD ${{ number_format($totalGeneralUsd, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla Comparativa Detallada -->
                        <h5 class="txt-primary mt-3 mb-2"><i class="fa fa-table"></i> Detalle por Operador</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered mt-1">
                                <thead class="text-white" style="background: #3b3f5c">
                                    <tr>
                                        <th class="table-th text-white">Operador</th>
                                        <th class="table-th text-white">Método</th>
                                        <th class="table-th text-white text-center">Moneda</th>
                                        <th class="table-th text-white text-right">Monto Original</th>
                                        <th class="table-th text-white text-right">Tasa Cambio</th>
                                        <th class="table-th text-white text-right">Equivalente USD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData as $sellerName => $payments)
                                        @php
                                            $sellerTotalUsd = 0;
                                        @endphp
                                        @foreach($payments as $index => $row)
                                            @php $sellerTotalUsd += $row->total_usd; @endphp
                                            <tr>
                                                @if($index === 0)
                                                    <td class="font-weight-bold bg-light align-middle" rowspan="{{ count($payments) + 1 }}">{{ $sellerName }}</td>
                                                @endif
                                                <td class="text-uppercase">{{ $row->method }}</td>
                                                <td class="text-center">{{ strtoupper($row->currency) }}</td>
                                                <td class="text-right">{{ number_format($row->total_amount, 2) }}</td>
                                                <td class="text-right">{{ number_format($row->avg_rate, 2) }}</td>
                                                <td class="text-right text-info">${{ number_format($row->total_usd, 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <!-- Subtotal por operador -->
                                        <tr class="bg-light">
                                            <td colspan="4" class="text-right font-weight-bold">SUBTOTAL OPERADOR:</td>
                                            <td class="text-right font-weight-bold text-success">${{ number_format($sellerTotalUsd, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No hay cobros registrados para los filtros seleccionados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($reportData->isNotEmpty())
                                    <tfoot style="background-color: #2c2f4a; font-weight: bold;">
                                        <tr>
                                            <td colspan="5" class="text-right text-white">TOTAL GENERAL COBRADO USD:</td>
                                            <td class="text-right text-success">${{ number_format($totalGeneralUsd, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Visor PDF -->
    @if ($showPdfModal)
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-xl" role="document" style="max-width: 90%; height: 90vh; margin: 30px auto;">
                <div class="modal-content" style="height: 100%;">
                    <div class="modal-header bg-dark p-2 text-white d-flex justify-content-between align-items-center">
                        <h5 class="modal-title text-white mb-0">
                            <i class="fas fa-file-pdf"></i> Vista Previa — Cobranza por Operador
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-sm btn-outline-light me-2">
                                <i class="fa fa-download"></i> Descargar
                            </a>
                            <button type="button" class="close text-white" wire:click.prevent="closePdfPreview" aria-label="Close" style="outline: none;">
                                <span aria-hidden="true" style="font-size: 24px;">&times;</span>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body p-0" style="height: calc(100% - 55px); overflow: hidden;">
                        <iframe src="{{ $pdfUrl }}" style="width: 100%; height: 100%; border: none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
