<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentCategoryRelationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_category_can_belong_to_a_department()
    {
        // 1. RED: Writing the test before Department model and migrations exist.
        $department = Department::create([
            'name' => 'Misceláneos',
            'report_type' => 'local',
        ]);

        $category = Category::create([
            'name' => 'Papelería General',
            'department_id' => $department->id,
        ]);

        $this->assertEquals($department->id, $category->department_id);
        $this->assertEquals('Misceláneos', $category->department->name);
        $this->assertEquals('local', $category->department->report_type);
    }

    /** @test */
    public function a_product_can_have_is_variable_price_attribute()
    {
        $category = Category::create([
            'name' => 'Servicios Libres',
        ]);

        $supplier = \App\Models\Supplier::create([
            'name' => 'Proveedor de Prueba',
        ]);

        $product = Product::create([
            'name' => 'Diseño Personalizado',
            'sku' => 'DIS-VAR-SKU',
            'code' => 'DIS-VAR',
            'barcode' => 'DIS-VAR-BAR',
            'cost' => 0.00,
            'price' => 0.00,
            'price2' => 0.00,
            'stock_qty' => 0,
            'manage_stock' => 0,
            'low_stock' => 0,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'is_variable_price' => true,
        ]);

        $this->assertTrue((bool)$product->is_variable_price);
    }

    /** @test */
    public function department_seeder_creates_default_departments_and_maps_existing_categories()
    {
        // Create pre-existing category before running seeder
        $oldCategory = Category::create([
            'name' => 'Old Legacy Category',
        ]);

        // Run seeder
        $this->seed(DepartmentSeeder::class);

        // Check departments created
        $this->assertDatabaseHas('departments', ['name' => 'Misceláneos', 'report_type' => 'local']);
        $this->assertDatabaseHas('departments', ['name' => 'Papelería', 'report_type' => 'local']);
        $this->assertDatabaseHas('departments', ['name' => 'Otros', 'report_type' => 'local']);
        $this->assertDatabaseHas('departments', ['name' => 'Servicios', 'report_type' => 'gravado']);

        // Check relation mapped
        $otrosDept = Department::where('name', 'Otros')->first();
        $oldCategory->refresh();
        $this->assertEquals($otrosDept->id, $oldCategory->department_id);
    }

    /** @test */
    public function it_allows_inline_department_creation_and_category_assignment_in_livewire()
    {
        $this->seed(DepartmentSeeder::class);

        \Livewire\Livewire::test(\App\Livewire\Categories::class)
            ->set('newDeptName', 'Artículos Deportivos')
            ->set('newDeptType', 'gravado')
            ->call('saveDepartment')
            ->assertSet('btnCreateDept', false)
            ->assertSet('newDeptName', '')
            ->assertSet('newDeptType', 'local');

        $this->assertDatabaseHas('departments', [
            'name' => 'Artículos Deportivos',
            'report_type' => 'gravado',
        ]);

        $newDept = Department::where('name', 'Artículos Deportivos')->first();
        $this->assertNotNull($newDept);
    }
}
