<div>
    <div class="row">
        <!-- Sidebar - Opciones de Consulta -->
        <div class="col-sm-12 col-md-3">
            <div class="card mb-3">
                <div class="p-1 card-header bg-dark">
                    <h5 class="text-center txt-light mb-0">Filtros de Reporte</h5>
                </div>

                <div class="card-body">
                    <!-- Selector de Vendedores -->
                    <div class="mt-2">
                        <span class="f-14"><b>Filtrar Vendedores</b></span>
                        <div class="border p-2 rounded mt-1" style="max-height: 180px; overflow-y: auto; background-color: #f8f9fa;">
                            @forelse ($sellersList as $seller)
                                <div class="custom-control custom-checkbox mb-1">
                                    <input type="checkbox" class="custom-control-input" id="seller_{{ $seller->id }}" value="{{ $seller->id }}" wire:model.live="selectedSellers">
                                    <label class="custom-control-label f-12" for="seller_{{ $seller->id }}">{{ $seller->name }}</label>
                                </div>
                            @empty
                                <div class="text-center text-muted f-12 py-2">No se encontraron vendedores</div>
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
                    <h5 class="txt-light mb-0">Ventas por Vendedor — Clasificación Local / Gravado</h5>
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
                        <h5 class="txt-primary mb-3"><i class="fa fa-info-circle"></i> Totales en USD</h5>
                        <div class="row">
                            <!-- Total Local -->
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm border-left border-info h-100">
                                    <div class="card-body p-3">
                                        <div class="f-12 text-muted uppercase font-weight-bold">Ventas Locales</div>
                                        <div class="f-11 text-muted">(Departamentos tipo LOCAL)</div>
                                        <div class="f-20 font-weight-bold text-info mt-2">
                                            USD ${{ number_format($totals['local_usd'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Total Gravado -->
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm border-left border-warning h-100">
                                    <div class="card-body p-3">
                                        <div class="f-12 text-muted uppercase font-weight-bold">Ventas Gravadas</div>
                                        <div class="f-11 text-muted">(Departamentos tipo GRAVADO)</div>
                                        <div class="f-20 font-weight-bold text-warning mt-2">
                                            USD ${{ number_format($totals['gravado_usd'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Total General -->
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm border-left border-success h-100">
                                    <div class="card-body p-3">
                                        <div class="f-12 text-muted uppercase font-weight-bold">Total General</div>
                                        <div class="f-11 text-muted">(Local + Gravado)</div>
                                        <div class="f-20 font-weight-bold text-success mt-2">
                                            USD ${{ number_format($totals['total_usd'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla Comparativa Detallada -->
                        <h5 class="txt-primary mt-3 mb-2"><i class="fa fa-table"></i> Detalle por Vendedor</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover mt-1">
                                <thead class="text-white" style="background: #3b3f5c">
                                    <tr>
                                        <th class="table-th text-white">Vendedor</th>
                                        <th class="table-th text-white text-center">Local (USD)</th>
                                        <th class="table-th text-white text-center">Gravado (USD)</th>
                                        <th class="table-th text-white text-center">Total (USD)</th>
                                        <th class="table-th text-white text-center"># Ventas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData as $row)
                                        <tr>
                                            <td class="font-weight-bold bg-light">{{ $row->seller_name }}</td>
                                            <td class="text-right text-info font-weight-bold">${{ number_format($row->local_usd, 2) }}</td>
                                            <td class="text-right text-warning font-weight-bold">${{ number_format($row->gravado_usd, 2) }}</td>
                                            <td class="text-right text-success font-weight-bold">${{ number_format($row->total_usd, 2) }}</td>
                                            <td class="text-center">{{ $row->sale_count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay datos de ventas para los filtros seleccionados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($reportData->isNotEmpty())
                                    <tfoot style="background-color: #2c2f4a; font-weight: bold;">
                                        <tr>
                                            <td class="text-white">TOTALES</td>
                                            <td class="text-right text-info">${{ number_format($totals['local_usd'], 2) }}</td>
                                            <td class="text-right text-warning">${{ number_format($totals['gravado_usd'], 2) }}</td>
                                            <td class="text-right text-success">${{ number_format($totals['total_usd'], 2) }}</td>
                                            <td class="text-center text-white">{{ $reportData->sum('sale_count') }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>

                        <!-- Nota aclaratoria -->
                        <div class="alert alert-secondary mt-3 f-12">
                            <i class="fa fa-info-circle me-1"></i>
                            <b>Nota:</b> Las columnas <b>Local</b> y <b>Gravado</b> son clasificaciones según el <b>departamento de la categoría del producto</b>.
                            No representan el tipo de pago ni si se aplicó IVA. Para cambiar la clasificación de un producto, editá su categoría en
                            <b>Registros Maestros → Categorías</b>.
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
                            <i class="fas fa-file-pdf"></i> Vista Previa — Ventas por Vendedor
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
