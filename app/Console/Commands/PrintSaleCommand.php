<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;
use App\Traits\PrintTrait;

class PrintSaleCommand extends Command
{
    use PrintTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:print-sale {saleId : The ID of the sale to print}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print a sale receipt to the configured ESC/POS printer (runs in background, does not block the web request)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $saleId = $this->argument('saleId');

        $sale = Sale::find($saleId);
        if (!$sale) {
            $this->error("Sale #{$saleId} not found.");
            return self::FAILURE;
        }

        try {
            $this->printSale($saleId);
            $this->info("Sale #{$saleId} printed successfully.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to print sale #{$saleId}: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
