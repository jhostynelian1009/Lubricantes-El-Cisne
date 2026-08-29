<?php

namespace Tests\Feature\Products;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::ADMIN,
            'active' => true,
        ]);
    }

    private function createEmployeeWithPermission(?string $permissionKey = 'products.manage'): User
    {
        $user = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'active' => true,
        ]);

        if ($permissionKey) {
            $permission = Permission::firstOrCreate([
                'key' => $permissionKey,
                'name' => 'Permission ' . $permissionKey,
                'assignable_to_employee' => true,
            ]);
            $user->permissions()->attach($permission);
        }

        return $user;
    }

    public function test_authorized_crud_for_products()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['active' => true]);

        // Index
        $response = $this->get(route('products.index'));
        $response->assertStatus(200);

        // Create form
        $response = $this->get(route('products.create'));
        $response->assertStatus(200);

        // Store
        $response = $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => '  ace-sint-5w30  ',
            'barcode' => ' 0786100123456 ',
            'name' => '  Aceite   Sintético   5W-30  ',
            'description' => 'Aceite sintético para motores a gasolina',
            'unit' => 'galon',
            'minimum_stock' => '5.000',
            'last_cost' => '25.50',
            'sale_price' => '35.00',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'sku' => 'ACE-SINT-5W30',
            'barcode' => '0786100123456',
            'name' => 'Aceite Sintético 5W-30',
            'unit' => 'galon',
            'current_stock' => '0.000',
            'active' => true,
        ]);

        $product = Product::where('sku', 'ACE-SINT-5W30')->first();

        // Show
        $response = $this->get(route('products.show', $product));
        $response->assertStatus(200);

        // Edit form
        $response = $this->get(route('products.edit', $product));
        $response->assertStatus(200);

        // Update
        $response = $this->put(route('products.update', $product), [
            'category_id' => $category->id,
            'sku' => 'ACE-SINT-5W30-NEW',
            'barcode' => '0786100123456',
            'name' => 'Aceite Sintético 5W-30 Premium',
            'unit' => 'galon',
            'minimum_stock' => '10.000',
            'last_cost' => '26.00',
            'sale_price' => '38.00',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sku' => 'ACE-SINT-5W30-NEW',
            'name' => 'Aceite Sintético 5W-30 Premium',
            'sale_price' => '38.00',
        ]);
    }

    public function test_new_product_starts_at_zero_stock()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['active' => true]);

        $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => 'PROD-ZERO',
            'name' => 'Producto Cero Stock',
            'unit' => 'litro',
            'sale_price' => '10.00',
        ]);

        $product = Product::where('sku', 'PROD-ZERO')->first();
        $this->assertEquals('0.000', (string)$product->current_stock);
    }

    public function test_manipulating_current_stock_in_request_input_does_not_modify_stock()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['active' => true]);

        $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => 'PROD-HACK',
            'name' => 'Producto Intento Hack Stock',
            'unit' => 'litro',
            'sale_price' => '10.00',
            'current_stock' => '9999.000',
        ]);

        $product = Product::where('sku', 'PROD-HACK')->first();
        $this->assertEquals('0.000', (string)$product->current_stock);

        // Intento en update
        $this->put(route('products.update', $product), [
            'category_id' => $category->id,
            'sku' => 'PROD-HACK',
            'name' => 'Producto Intento Hack Stock Edit',
            'unit' => 'litro',
            'sale_price' => '12.00',
            'current_stock' => '555.000',
        ]);

        $this->assertEquals('0.000', (string)$product->fresh()->current_stock);
    }

    public function test_sku_is_normalized_and_unique()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['active' => true]);

        Product::factory()->create(['sku' => 'ACEITE-5W30', 'category_id' => $category->id]);

        $response = $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => '  aceite - 5w30  ',
            'name' => 'Aceite Duplicado',
            'unit' => 'galon',
            'sale_price' => '20.00',
        ]);

        $response->assertSessionHasErrors(['sku']);
    }

    public function test_barcode_is_nullable_and_unique_when_exists()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['active' => true]);

        Product::factory()->create(['barcode' => '786000111222', 'category_id' => $category->id]);

        // Duplicate barcode
        $response = $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => 'PROD-NEW-BARCODE',
            'barcode' => ' 786000111222 ', // padded barcode matching existing
            'name' => 'Producto Mismo Código',
            'unit' => 'galon',
            'sale_price' => '15.00',
        ]);

        $response->assertSessionHasErrors(['barcode']);

        // Nullable barcode allows multiple nulls
        $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => 'PROD-NO-BARCODE-1',
            'barcode' => '',
            'name' => 'Producto Sin Barcode 1',
            'unit' => 'litro',
            'sale_price' => '5.00',
        ]);

        $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => 'PROD-NO-BARCODE-2',
            'barcode' => null,
            'name' => 'Producto Sin Barcode 2',
            'unit' => 'litro',
            'sale_price' => '5.00',
        ]);

        $this->assertDatabaseHas('products', ['sku' => 'PROD-NO-BARCODE-1', 'barcode' => null]);
        $this->assertDatabaseHas('products', ['sku' => 'PROD-NO-BARCODE-2', 'barcode' => null]);
    }

    public function test_inactive_category_is_rejected_for_new_product()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $inactiveCategory = Category::factory()->create(['active' => false]);

        $response = $this->post(route('products.store'), [
            'category_id' => $inactiveCategory->id,
            'sku' => 'PROD-INACTIVE-CAT',
            'name' => 'Producto Con Categoría Inactiva',
            'unit' => 'galon',
            'sale_price' => '25.00',
        ]);

        $response->assertSessionHasErrors(['category_id']);
    }

    public function test_invalid_unit_is_rejected()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['active' => true]);

        $response = $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => 'PROD-BAD-UNIT',
            'name' => 'Producto Unidad Inválida',
            'unit' => 'unidad_inventada_inexistente',
            'sale_price' => '10.00',
        ]);

        $response->assertSessionHasErrors(['unit']);
    }

    public function test_sale_price_and_minimum_stock_are_validated()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['active' => true]);

        // Sale price 0 or negative
        $response = $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => 'PROD-BAD-PRICE',
            'name' => 'Producto Precio Cero',
            'unit' => 'galon',
            'sale_price' => '0.00',
        ]);
        $response->assertSessionHasErrors(['sale_price']);

        // Minimum stock > 3 decimals
        $response = $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => 'PROD-BAD-STOCK',
            'name' => 'Producto Stock Mínimo Inválido',
            'unit' => 'galon',
            'minimum_stock' => '1.2345',
            'sale_price' => '10.00',
        ]);
        $response->assertSessionHasErrors(['minimum_stock']);
    }

    public function test_product_activation_and_deactivation()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $product = Product::factory()->create(['active' => true]);

        $this->post(route('products.toggle-status', $product));
        $this->assertFalse($product->fresh()->active);

        $this->post(route('products.toggle-status', $product));
        $this->assertTrue($product->fresh()->active);
    }

    public function test_physical_deletion_route_does_not_exist()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $product = Product::factory()->create();

        $response = $this->delete("/products/{$product->id}");
        $response->assertStatus(405);
    }

    public function test_search_filters_and_pagination_preserved()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['name' => 'Aceites de Motor', 'active' => true]);

        for ($i = 1; $i <= 20; $i++) {
            Product::factory()->create([
                'category_id' => $category->id,
                'name' => "Aceite Sintético Multigrado {$i}",
                'sku' => "ACE-MULTI-{$i}",
                'active' => true,
            ]);
        }

        $response = $this->get(route('products.index', [
            'search' => 'Multigrado',
            'category_id' => $category->id,
            'status' => 'active',
            'page' => 2,
        ]));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertInstanceOf(LengthAwarePaginator::class, $products);
        $this->assertEquals(2, $products->currentPage());

        $content = $response->getContent();
        $this->assertTrue(str_contains($content, 'search=Multigrado'));
        $this->assertTrue(str_contains($content, 'status=active'));
    }

    public function test_stock_status_calculation()
    {
        $category = Category::factory()->create(['active' => true]);

        $outOfStock = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => '0.000',
            'minimum_stock' => '5.000',
        ]);

        $lowStock = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => '3.000',
            'minimum_stock' => '5.000',
        ]);

        $normalStock = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => '10.000',
            'minimum_stock' => '5.000',
        ]);

        $this->assertEquals('out_of_stock', $outOfStock->stock_status);
        $this->assertEquals('Agotado', $outOfStock->stock_status_label);

        $this->assertEquals('low_stock', $lowStock->stock_status);
        $this->assertEquals('Bajo stock', $lowStock->stock_status_label);

        $this->assertEquals('normal', $normalStock->stock_status);
        $this->assertEquals('Stock normal', $normalStock->stock_status_label);
    }

    public function test_no_n_plus_one_query_problem_with_categories()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['active' => true]);
        Product::factory()->count(10)->create(['category_id' => $category->id]);

        DB::enableQueryLog();

        $response = $this->get(route('products.index'));
        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        // Index queries should be limited: count query, page query, categories query, user/permission queries. Not 10 per product.
        $this->assertLessThan(10, count($queries));
    }

    public function test_spanish_characters_and_accents_are_saved_correctly()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['name' => 'Transmisión Manual', 'active' => true]);

        $response = $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => 'VAL-SINT-75W90',
            'name' => 'Valvolina Sintética «Super-Transmisión» 75W-90 (¡Edición Especial!)',
            'description' => 'Fluido para caja de cambios con especificación GL-5: óptimo rendimiento.',
            'unit' => 'litro',
            'minimum_stock' => '2.000',
            'sale_price' => '18.50',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'sku' => 'VAL-SINT-75W90',
            'name' => 'Valvolina Sintética «Super-Transmisión» 75W-90 (¡Edición Especial!)',
            'description' => 'Fluido para caja de cambios con especificación GL-5: óptimo rendimiento.',
        ]);
    }

    public function test_barcode_preserves_leading_zeros()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['active' => true]);

        $this->post(route('products.store'), [
            'category_id' => $category->id,
            'sku' => 'PROD-ZERO-BARCODE',
            'barcode' => '001234567890',
            'name' => 'Producto Código Con Ceros Iniciales',
            'unit' => 'litro',
            'sale_price' => '10.00',
        ]);

        $product = Product::where('sku', 'PROD-ZERO-BARCODE')->first();
        $this->assertSame('001234567890', $product->barcode);
        $this->assertDatabaseHas('products', [
            'sku' => 'PROD-ZERO-BARCODE',
            'barcode' => '001234567890',
        ]);
    }
}
