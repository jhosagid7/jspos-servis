<?php

namespace Tests\Feature;

use App\Livewire\Sales;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Configuration;
use App\Models\Customer;
use App\Models\CustomerConfig;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class POSPricePreservationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $customer;
    protected $product;
    protected $variableProduct;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.installed' => false,
            'tenant.modules' => ['module_credits', 'module_commissions'],
        ]);

        // Reset static cache in ConfigurationService
        $ref = new \ReflectionClass(\App\Services\ConfigurationService::class);
        $prop = $ref->getProperty('config');
        $prop->setAccessible(true);
        $prop->setValue(null);

        $warehouse = \App\Models\Warehouse::create([
            'id' => 1,
            'name' => 'TIENDA PRINCIPAL',
            'is_active' => 1,
        ]);

        Configuration::create([
            'business_name' => 'POS Price preservation Test Business',
            'decimals' => 2,
            'vat' => 16,
            'bcv_rate' => 54.50,
            'binance_rate' => 70.00,
            'binance_markup_points' => 5.00,
            'default_warehouse_id' => $warehouse->id,
        ]);

        $this->seed(\Database\Seeders\CurrencySeeder::class);

        $this->user = User::factory()->create();
        
        CashRegister::create([
            'user_id' => $this->user->id,
            'status' => 'open',
            'opening_date' => now(),
            'total_opening_amount' => 1000.00,
        ]);

        $this->customer = Customer::create([
            'name' => 'Test POS Customer',
            'type' => 'Consumidor Final',
            'allow_credit' => true,
            'credit_days' => 15,
            'credit_limit' => 1000.00,
            'taxpayer_id' => 'V99999999',
            'address' => 'Customer Address',
            'city' => 'Caracas',
            'phone' => '0412-1111111',
            'email' => 'customer@email.com',
            'seller_id' => $this->user->id,
        ]);
        
        CustomerConfig::create([
            'customer_id' => $this->customer->id,
            'commission_percent' => 0.00,
            'freight_percent' => 0.00,
            'exchange_diff_percent' => 0.00,
        ]);

        $category = Category::create([
            'name' => 'Test Category POS',
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier POS',
            'taxpayer_id' => 'J88888888',
            'address' => 'Supplier Address POS',
            'phone' => '0212-2222222',
        ]);

        // Standard Product
        $this->product = Product::create([
            'name' => 'POS Test Product',
            'sku' => 'POS-TEST-001',
            'cost' => 10.00,
            'price' => 10.00,
            'price1' => 10.00,
            'price_usd' => 10.00,
            'show_in_sales' => true,
            'manage_stock' => false,
            'stock_qty' => 100,
            'low_stock' => 0,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        // Variable Price Product (Diseño)
        $this->variableProduct = Product::create([
            'name' => 'Diseño Personalizado',
            'sku' => 'POS-VAR-001',
            'cost' => 0.00,
            'price' => 0.00,
            'price1' => 0.00,
            'price_usd' => 0.00,
            'show_in_sales' => true,
            'manage_stock' => false,
            'stock_qty' => 1,
            'low_stock' => 0,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'is_variable_price' => true,
        ]);
    }

    /** @test */
    public function custom_price_survives_quantity_updates_and_recalculations()
    {
        $this->actingAs($this->user);

        // 1. Load Livewire POS component
        $lw = Livewire::test(Sales::class);

        // Set customer
        $lw->call('setCustomer', $this->customer->toArray());

        // 2. Add product
        $lw->call('AddProduct', $this->product);
        
        $lw->assertCount('cart', 1);
        $cartItem = $lw->get('cart')->first();
        $this->assertEquals(10.00, $cartItem['sale_price']);

        // 3. Set custom price
        $lw->call('setCustomPrice', $cartItem['id'], 15.00);

        $cartItem = $lw->get('cart')->first();
        $this->assertEquals(15.00, $cartItem['sale_price']);
        $this->assertTrue($cartItem['is_custom_price']);

        // 4. Update Qty - Custom price must survive!
        $lw->call('updateQty', $cartItem['id'], 3);

        $cartItem = $lw->get('cart')->first();
        $this->assertEquals(15.00, $cartItem['sale_price']);
        $this->assertEquals(45.00, $cartItem['total']);
        $this->assertTrue($cartItem['is_custom_price']);

        // 5. Recalculate - Custom price must survive!
        $lw->call('recalculateCartPrices');

        $cartItem = $lw->get('cart')->first();
        $this->assertEquals(15.00, $cartItem['sale_price']);
        $this->assertTrue($cartItem['is_custom_price']);
    }

    /** @test */
    public function variable_price_product_prompts_and_sets_custom_price()
    {
        $this->actingAs($this->user);

        $lw = Livewire::test(Sales::class);
        $lw->call('setCustomer', $this->customer->toArray());

        // 1. Try to add variable product -> should trigger prompt-variable-price event
        $lw->call('AddProduct', $this->variableProduct);

        $lw->assertCount('cart', 0); // Not added yet
        $lw->assertDispatched('prompt-variable-price');

        // 2. Simulate setting variable price from prompt
        $lw->call('setVariablePriceAndAdd', 25.50);

        $lw->assertCount('cart', 1);
        $cartItem = $lw->get('cart')->first();
        $this->assertEquals(25.50, $cartItem['sale_price']);
        $this->assertTrue($cartItem['is_custom_price']);
    }
}
