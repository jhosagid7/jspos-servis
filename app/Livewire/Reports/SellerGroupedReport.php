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
    public $selectedOperators = [];
    public $dateFrom = '';
    public $dateTo = '';
    public $showReport = false;
    public $showPdfModal = false;
    public $pdfUrl = '';

    public function mount()
    {
        session(['pos' => 'Reporte Cobranza por Operador']);
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

        // 1. Pagos en POS (sale_payment_details)
        $posPaymentsQuery = DB::table('sale_payment_details')
            ->join('sales', 'sale_payment_details.sale_id', '=', 'sales.id')
            ->leftJoin('users', 'sales.user_id', '=', 'users.id')
            ->where('sales.status', '<>', 'returned')
            ->whereNull('sales.deletion_approved_at');

        if ($this->dateFrom) {
            $posPaymentsQuery->where('sale_payment_details.created_at', '>=', $this->dateFrom . ' 00:00:00');
        }
        if ($this->dateTo) {
            $posPaymentsQuery->where('sale_payment_details.created_at', '<=', $this->dateTo . ' 23:59:59');
        }
        if (!empty($this->selectedOperators)) {
            $posPaymentsQuery->whereIn('sales.user_id', $this->selectedOperators);
        }

        $posPayments = $posPaymentsQuery->select([
            'sales.user_id',
            DB::raw("COALESCE(users.name, 'SISTEMA / ONLINE') as seller_name"),
            'sale_payment_details.payment_method as method',
            'sale_payment_details.currency_code as currency',
            DB::raw("SUM(sale_payment_details.amount) as total_amount"),
            DB::raw("AVG(sale_payment_details.exchange_rate) as avg_rate"),
            DB::raw("SUM(sale_payment_details.amount_in_primary_currency) as total_usd")
        ])->groupBy('sales.user_id', 'users.name', 'sale_payment_details.payment_method', 'sale_payment_details.currency_code')->get();

        // 2. Abonos (payments)
        $abonosQuery = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->leftJoin('users', 'payments.user_id', '=', 'users.id')
            ->where('sales.status', '<>', 'returned')
            ->whereNull('sales.deletion_approved_at')
            ->where('payments.status', 'approved');

        if ($this->dateFrom) {
            $abonosQuery->where('payments.payment_date', '>=', $this->dateFrom . ' 00:00:00');
        }
        if ($this->dateTo) {
            $abonosQuery->where('payments.payment_date', '<=', $this->dateTo . ' 23:59:59');
        }
        if (!empty($this->selectedOperators)) {
            $abonosQuery->whereIn('payments.user_id', $this->selectedOperators);
        }

        $abonos = $abonosQuery->select([
            'payments.user_id',
            DB::raw("COALESCE(users.name, 'SISTEMA / ONLINE') as seller_name"),
            'payments.pay_way as method',
            'payments.currency as currency',
            DB::raw("SUM(payments.amount) as total_amount"),
            DB::raw("AVG(payments.exchange_rate) as avg_rate"),
            DB::raw("SUM(payments.amount / CASE WHEN payments.exchange_rate > 0 THEN payments.exchange_rate ELSE 1 END) as total_usd")
        ])->groupBy('payments.user_id', 'users.name', 'payments.pay_way', 'payments.currency')->get();

        $all = $posPayments->concat($abonos);

        // Agrupar por vendedor
        return $all->groupBy('seller_name')->map(function($sellerPayments) {
            return $sellerPayments->groupBy(function($item) {
                return $item->method . '-' . $item->currency;
            })->map(function($methodGroup) {
                $first = $methodGroup->first();
                return (object)[
                    'method' => $first->method,
                    'currency' => $first->currency,
                    'total_amount' => $methodGroup->sum('total_amount'),
                    'avg_rate' => $methodGroup->avg('avg_rate'),
                    'total_usd' => $methodGroup->sum('total_usd'),
                ];
            })->values();
        });
    }

    public function generatePdf()
    {
        $reportData = $this->getReportData();
        
        $totalGeneralUsd = 0;
        foreach ($reportData as $sellerName => $payments) {
            foreach ($payments as $p) {
                $totalGeneralUsd += $p->total_usd;
            }
        }

        $config = \App\Models\Configuration::first();

        $pdf = Pdf::loadView('reports.seller-grouped-report-pdf', [
            'reportData'  => $reportData,
            'totalGeneralUsd' => $totalGeneralUsd,
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
            'selectedOperators' => implode(',', $this->selectedOperators),
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
        $operatorsList = User::orderBy('name')->get();
        $reportData  = $this->getReportData();

        $totalGeneralUsd = 0;
        // Also calculate totals by method/currency for the top cards
        $totalsByMethod = [];
        
        foreach ($reportData as $sellerName => $payments) {
            foreach ($payments as $p) {
                $totalGeneralUsd += $p->total_usd;
                
                $key = $p->method . '-' . $p->currency;
                if (!isset($totalsByMethod[$key])) {
                    $totalsByMethod[$key] = [
                        'method' => $p->method,
                        'currency' => $p->currency,
                        'total_amount' => 0,
                        'total_usd' => 0
                    ];
                }
                $totalsByMethod[$key]['total_amount'] += $p->total_amount;
                $totalsByMethod[$key]['total_usd'] += $p->total_usd;
            }
        }

        return view('livewire.reports.seller-grouped-report', [
            'operatorsList' => $operatorsList,
            'reportData'  => $reportData,
            'totalGeneralUsd' => $totalGeneralUsd,
            'totalsByMethod' => $totalsByMethod
        ]);
    }
}
