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

                    <!-- Botones de Acción -->
                    <div class="mt-4">
                        <button wire:key="btn-seller-grouped-search" wire:click.prevent="searchData" class="btn btn-dark w-100">
                            <i class="fa fa-sync"></i> Generar Reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de Resultados -->
        <div class="col-sm-12 col-md-9">
            <div class="card card-absolute">
                <div class="card-header bg-dark">
                    <h5 class="txt-light">Ventas por Vendedor - Desglose Local y Gravado</h5>
                </div>

                <div class="card-body">
                    <!-- Mensaje de instrucción -->
                    <div class="alert alert-info text-center {{ !$showReport ? '' : 'd-none' }}">
                        Selecciona los filtros en la barra lateral y haz clic en "Generar Reporte" para visualizar las ventas.
                    </div>

                    <!-- Panel de Resultados -->
                    <div class="{{ !$showReport ? 'd-none' : '' }}">
                        
                        <!-- KPIs de Resumen -->
                        <h5 class="txt-primary mb-3"><i class="fa fa-info-circle"></i> Totales Consolidados</h5>
                        <div class="row">
                            <!-- Total Local -->
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm border-left border-info h-100">
                                    <div class="card-body p-3">
                                        <div class="f-12 text-muted uppercase font-weight-bold">Ventas Locales (Sin IVA)</div>
                                        <div class="f-18 font-weight-bold text-dark mt-1">
                                            Bs. {{ number_format($totals['local_bs'], 2) }}
                                        </div>
                                        <div class="f-14 font-weight-bold text-info">
                                            USD ${{ number_format($totals['local_usd'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Total Gravado -->
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm border-left border-warning h-100">
                                    <div class="card-body p-3">
                                        <div class="f-12 text-muted uppercase font-weight-bold">Ventas Gravadas (Con IVA)</div>
                                        <div class="f-18 font-weight-bold text-dark mt-1">
                                            Bs. {{ number_format($totals['gravado_bs'], 2) }}
                                        </div>
                                        <div class="f-14 font-weight-bold text-warning">
                                            USD ${{ number_format($totals['gravado_usd'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Total General -->
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm border-left border-success h-100">
                                    <div class="card-body p-3">
                                        <div class="f-12 text-muted uppercase font-weight-bold">Ventas Totales Netas</div>
                                        <div class="f-18 font-weight-bold text-dark mt-1">
                                            Bs. {{ number_format($totals['total_bs'], 2) }}
                                        </div>
                                        <div class="f-14 font-weight-bold text-success">
                                            USD ${{ number_format($totals['total_usd'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla Comparativa Detallada -->
                        <h5 class="txt-primary mt-3 mb-2"><i class="fa fa-table"></i> Ventas Agrupadas por Vendedor</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover mt-1">
                                <thead class="text-white" style="background: #3b3f5c">
                                    <tr>
                                        <th class="table-th text-white">Vendedor</th>
                                        <th class="table-th text-white text-center">Local (Bs.)</th>
                                        <th class="table-th text-white text-center">Local (USD)</th>
                                        <th class="table-th text-white text-center">Gravado (Bs.)</th>
                                        <th class="table-th text-white text-center">Gravado (USD)</th>
                                        <th class="table-th text-white text-center">Total (Bs.)</th>
                                        <th class="table-th text-white text-center">Total (USD)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData as $row)
                                        <tr>
                                            <td class="font-weight-bold bg-light">{{ $row->seller_name }}</td>
                                            <td class="text-right">Bs. {{ number_format($row->local_bs, 2) }}</td>
                                            <td class="text-right text-info font-weight-bold">${{ number_format($row->local_usd, 2) }}</td>
                                            <td class="text-right">Bs. {{ number_format($row->gravado_bs, 2) }}</td>
                                            <td class="text-right text-warning font-weight-bold">${{ number_format($row->gravado_usd, 2) }}</td>
                                            <td class="text-right font-weight-bold">Bs. {{ number_format($row->total_bs, 2) }}</td>
                                            <td class="text-right text-success font-weight-bold">${{ number_format($row->total_usd, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No hay datos de ventas para los filtros seleccionados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($reportData->isNotEmpty())
                                    <tfoot style="background-color: #f1f2f3; font-weight: bold;">
                                        <tr>
                                            <td>TOTALES</td>
                                            <td class="text-right">Bs. {{ number_format($totals['local_bs'], 2) }}</td>
                                            <td class="text-right text-info">USD ${{ number_format($totals['local_usd'], 2) }}</td>
                                            <td class="text-right">Bs. {{ number_format($totals['gravado_bs'], 2) }}</td>
                                            <td class="text-right text-warning">USD ${{ number_format($totals['gravado_usd'], 2) }}</td>
                                            <td class="text-right">Bs. {{ number_format($totals['total_bs'], 2) }}</td>
                                            <td class="text-right text-success">USD ${{ number_format($totals['total_usd'], 2) }}</td>
                                        </tr>
                                    </footer>
                                @endif
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
