<?php

namespace Tests\Feature;

use App\Livewire\Reports\SellerGroupedReport;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SellerGroupedReportTest extends TestCase
{
    use RefreshDatabase;

    protected $seller;
    protected $customer;
    protected $warehouse;
    protected $localProduct;
    protected $gravadoProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create(['name' => 'Vendedor Estrella']);
        
        // Ensure role or scope logic works for sellers
        // Note: scopeSellers in User model might filter by role or attributes.
        // Let's check how scopeSellers is defined in User model. We saw it's there.
        // In case it filters by role, let's make sure it returns this user.
        // Let's inspect scopeSellers in User.php or assign permission/role if needed.

        $this->customer = Customer::create([
            'name' => 'Cliente de Prueba',
            'type' => 'Consumidor Final',
            'seller_id' => $this->seller->id,
            'taxpayer_id' => 'V12345678',
            'address' => 'Direccion',
            'phone' => '0414-1234567',
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Deposito Test',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'name' => 'Proveedor Test',
            'taxpayer_id' => 'J12345678',
            'address' => 'Direccion Proveedor',
            'phone' => '0212-1234567',
        ]);

        // Departments
        $localDept = Department::create(['name' => 'Servicios Locales', 'report_type' => 'local']);
        $gravadoDept = Department::create(['name' => 'Productos Gravados', 'report_type' => 'gravado']);

        // Categories
        $localCat = Category::create(['name' => 'Servicios', 'department_id' => $localDept->id]);
        $gravadoCat = Category::create(['name' => 'Ferreteria', 'department_id' => $gravadoDept->id]);

        // Products
        $this->localProduct = Product::create([
            'name' => 'Diseño Web',
            'sku' => 'PROD-LOC-1',
            'cost' => 10.00,
            'price' => 100.00,
            'stock_qty' => 10,
            'manage_stock' => 0,
            'low_stock' => 0,
            'category_id' => $localCat->id,
            'supplier_id' => $supplier->id,
        ]);

        $this->gravadoProduct = Product::create([
            'name' => 'Tornillo',
            'sku' => 'PROD-GRA-1',
            'cost' => 1.00,
            'price' => 50.00,
            'stock_qty' => 100,
            'manage_stock' => 0,
            'low_stock' => 0,
            'category_id' => $gravadoCat->id,
            'supplier_id' => $supplier->id,
        ]);
    }

    /** @test */
    public function it_groups_sales_by_seller_and_department_report_type()
    {
        $this->actingAs($this->seller);

        // 1. Create a Sale
        $sale = Sale::create([
            'customer_id' => $this->customer->id,
            'user_id' => $this->seller->id,
            'total' => 200.00,
            'total_usd' => 4.00,
            'items' => 2,
            'type' => 'cash',
            'status' => 'paid',
            'primary_exchange_rate' => 50.00, // exchange rate for USD conversion
        ]);

        // Local item: Qty 1, price 100.00 (Bs. 100 / $2.00)
        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $this->localProduct->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'regular_price' => 100.00,
            'sale_price' => 100.00,
            'discount' => 0.00,
        ]);

        // Gravado item: Qty 2, price 50.00 (Bs. 100 / $2.00)
        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $this->gravadoProduct->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 2,
            'regular_price' => 50.00,
            'sale_price' => 50.00,
            'discount' => 0.00,
        ]);

        // 2. Test Livewire report
        $lw = Livewire::test(SellerGroupedReport::class);
        
        $lw->call('searchData');

        $reportData = $lw->instance()->getReportData();
        
        $this->assertCount(1, $reportData);

        $row = $reportData->first();
        $this->assertEquals('Vendedor Estrella', $row->seller_name);
        $this->assertEquals(100.00, $row->local_bs);
        $this->assertEquals(2.00, $row->local_usd);
        $this->assertEquals(100.00, $row->gravado_bs);
        $this->assertEquals(2.00, $row->gravado_usd);
        $this->assertEquals(200.00, $row->total_bs);
        $this->assertEquals(4.00, $row->total_usd);
    }
}
