<?php

namespace App\Livewire\Reports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class SellerGroupedReport extends Component
{
    public $selectedSellers = [];
    public $dateFrom = '';
    public $dateTo = '';
    public $showReport = false;
    public $showPdfModal = false;
    public $pdfUrl = '';

    public function mount()
    {
        session(['pos' => 'Reporte Agrupado por Vendedor']);
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = Carbon::now()->format('Y-m-d');
    }

    public function setToday()
    {
        $this->dateFrom = Carbon::today()->format('Y-m-d');
        $this->dateTo   = Carbon::today()->format('Y-m-d');
        $this->searchData();
    }

    public function searchData()
    {
        $this->showReport = true;
        $this->dispatch('noty', msg: 'REPORTE ACTUALIZADO');
    }

    public function getReportData()
    {
        if (!$this->showReport) {
            return collect([]);
        }

        $query = DB::table('sale_details')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->leftJoin('products', 'sale_details.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('departments', 'categories.department_id', '=', 'departments.id')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.seller_id', '=', 'users.id')
            ->where('sales.status', '<>', 'returned')
            ->whereNull('sales.deletion_approved_at');

        if ($this->dateFrom) {
            $query->where('sales.created_at', '>=', $this->dateFrom . ' 00:00:00');
        }
        if ($this->dateTo) {
            $query->where('sales.created_at', '<=', $this->dateTo . ' 23:59:59');
        }

        if (!empty($this->selectedSellers)) {
            $query->whereIn('customers.seller_id', $this->selectedSellers);
        }

        return $query->select([
                'customers.seller_id',
                DB::raw("COALESCE(users.name, 'OFICINA / SIN VENDEDOR') as seller_name"),
                DB::raw("SUM(CASE WHEN departments.report_type = 'local' THEN sale_details.quantity * sale_details.sale_price ELSE 0 END) as local_usd"),
                DB::raw("SUM(CASE WHEN departments.report_type = 'gravado' THEN sale_details.quantity * sale_details.sale_price ELSE 0 END) as gravado_usd"),
                DB::raw("SUM(sale_details.quantity * sale_details.sale_price) as total_usd"),
                DB::raw("COUNT(DISTINCT sale_details.sale_id) as sale_count")
            ])
            ->groupBy(['customers.seller_id', 'users.name'])
            ->orderBy('users.name')
            ->get();
    }

    public function generatePdf()
    {
        $reportData = $this->getReportData();
        $totals = [
            'local_usd'   => $reportData->sum('local_usd'),
            'gravado_usd' => $reportData->sum('gravado_usd'),
            'total_usd'   => $reportData->sum('total_usd'),
        ];

        $config = \App\Models\Configuration::first();

        $pdf = Pdf::loadView('reports.seller-grouped-report-pdf', [
            'reportData'  => $reportData,
            'totals'      => $totals,
            'config'      => $config,
            'dateFrom'    => $this->dateFrom,
            'dateTo'      => $this->dateTo,
            'generatedAt' => Carbon::now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        $filename = 'Reporte_Vendedores_'
            . Carbon::parse($this->dateFrom)->format('Ymd') . '_'
            . Carbon::parse($this->dateTo)->format('Ymd') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function openPdfPreview()
    {
        $params = [
            'dateFrom'        => $this->dateFrom,
            'dateTo'          => $this->dateTo,
            'selectedSellers' => implode(',', $this->selectedSellers),
        ];

        $this->pdfUrl = route('reports.seller.grouped.pdf', $params);
        $this->showPdfModal = true;
    }

    public function closePdfPreview()
    {
        $this->showPdfModal = false;
        $this->pdfUrl = '';
    }

    public function render()
    {
        $sellersList = User::sellers()->orderBy('name')->get();
        $reportData  = $this->getReportData();

        $totals = [
            'local_usd'   => $reportData->sum('local_usd'),
            'gravado_usd' => $reportData->sum('gravado_usd'),
            'total_usd'   => $reportData->sum('total_usd'),
        ];

        return view('livewire.reports.seller-grouped-report', [
            'sellersList' => $sellersList,
            'reportData'  => $reportData,
            'totals'      => $totals,
        ]);
    }
}
