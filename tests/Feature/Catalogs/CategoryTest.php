<?php

namespace Tests\Feature\Catalogs;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::ADMIN,
            'active' => true,
        ]);
    }

    private function createEmployeeWithPermission(?string $permissionKey = 'categories.manage'): User
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

    public function test_admin_can_list_create_view_and_edit_categories()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        // Index
        $response = $this->get(route('categories.index'));
        $response->assertStatus(200);

        // Create form
        $response = $this->get(route('categories.create'));
        $response->assertStatus(200);

        // Store
        $response = $this->post(route('categories.store'), [
            'name' => 'Aceites Sintéticos',
            'description' => 'Aceites de alto rendimiento',
        ]);
        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('categories', [
            'name' => 'Aceites Sintéticos',
            'active' => true,
        ]);

        $category = Category::where('name', 'Aceites Sintéticos')->first();

        // Show
        $response = $this->get(route('categories.show', $category));
        $response->assertStatus(200);

        // Edit form
        $response = $this->get(route('categories.edit', $category));
        $response->assertStatus(200);

        // Update
        $response = $this->put(route('categories.update', $category), [
            'name' => 'Aceites Sintéticos Premium',
            'description' => 'Descripción actualizada',
        ]);
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Aceites Sintéticos Premium',
        ]);
    }

    public function test_employee_with_permission_can_manage_categories()
    {
        $employee = $this->createEmployeeWithPermission('categories.manage');
        $this->actingAs($employee);

        $response = $this->post(route('categories.store'), [
            'name' => 'Filtros de Aire',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Filtros de Aire']);
    }

    public function test_employee_without_permission_receives_403()
    {
        $employee = $this->createEmployeeWithPermission(null);
        $this->actingAs($employee);

        $response = $this->get(route('categories.index'));
        $response->assertStatus(403);

        $response = $this->post(route('categories.store'), ['name' => 'Prueba 403']);
        $response->assertStatus(403);
    }

    public function test_category_name_is_required()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post(route('categories.store'), [
            'name' => '   ',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_duplicate_names_with_spaces_or_case_differences_are_rejected()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        Category::factory()->create(['name' => 'Aceites de Motor']);

        // Extra spaces and lower-case variation
        $response = $this->post(route('categories.store'), [
            'name' => '  aceites  de  motor  ',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_search_and_status_filters_work_properly()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $cat1 = Category::factory()->create(['name' => 'Lubricantes Hidráulicos', 'active' => true]);
        $cat2 = Category::factory()->create(['name' => 'Lubricantes de Transmisión', 'active' => false]);
        $cat3 = Category::factory()->create(['name' => 'Grasas Multiuso', 'active' => true]);

        // Search by name
        $response = $this->get(route('categories.index', ['search' => 'Lubricantes']));
        $response->assertStatus(200);
        $response->assertSee('Lubricantes Hidráulicos');
        $response->assertSee('Lubricantes de Transmisión');
        $response->assertDontSee('Grasas Multiuso');

        // Filter by active status
        $response = $this->get(route('categories.index', ['status' => 'active']));
        $response->assertStatus(200);
        $response->assertSee('Lubricantes Hidráulicos');
        $response->assertSee('Grasas Multiuso');
        $response->assertDontSee('Lubricantes de Transmisión');
    }

    public function test_category_activation_and_deactivation()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create(['active' => true]);

        // Toggle to inactive
        $response = $this->post(route('categories.toggle-status', $category));
        $response->assertRedirect();
        $this->assertFalse($category->fresh()->active);

        // Toggle back to active
        $response = $this->post(route('categories.toggle-status', $category));
        $response->assertRedirect();
        $this->assertTrue($category->fresh()->active);
    }

    public function test_destroy_route_does_not_exist()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::factory()->create();

        $response = $this->delete("/categories/{$category->id}");
        $response->assertStatus(405);
    }

    public function test_active_scope_excludes_inactive_categories()
    {
        Category::factory()->create(['name' => 'Activa 1', 'active' => true]);
        Category::factory()->create(['name' => 'Inactiva 1', 'active' => false]);

        $activeCategories = Category::active()->get();

        $this->assertCount(1, $activeCategories);
        $this->assertEquals('Activa 1', $activeCategories->first()->name);
    }

    public function test_spanish_characters_and_accents_are_saved_correctly()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post(route('categories.store'), [
            'name' => 'Líquidos de Frenos «Ñandú» (¡Atención!)',
            'description' => 'Descripción con tildes: árbol, camión, éxito.',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Líquidos de Frenos «Ñandú» (¡Atención!)',
            'description' => 'Descripción con tildes: árbol, camión, éxito.',
        ]);
    }

    public function test_category_index_is_paginated()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        Category::factory()->count(20)->create();

        $response = $this->get(route('categories.index'));

        $response->assertStatus(200);
        $categories = $response->viewData('categories');
        $this->assertInstanceOf(LengthAwarePaginator::class, $categories);
        $this->assertEquals(20, $categories->total());
        $this->assertEquals(15, $categories->perPage());
    }

    public function test_category_pagination_preserves_search_and_status_filters()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        // Create 20 matching categories
        for ($i = 1; $i <= 20; $i++) {
            Category::factory()->create([
                'name' => "Filtro Especial {$i}",
                'active' => true,
            ]);
        }

        $response = $this->get(route('categories.index', [
            'search' => 'Filtro Especial',
            'status' => 'active',
            'page' => 2,
        ]));

        $response->assertStatus(200);
        $categories = $response->viewData('categories');
        $this->assertInstanceOf(LengthAwarePaginator::class, $categories);
        $this->assertEquals(2, $categories->currentPage());

        $content = $response->getContent();
        $this->assertTrue(str_contains($content, 'search=Filtro%20Especial') || str_contains($content, 'search=Filtro+Especial'));
        $this->assertStringContainsString('status=active', $content);
    }
}
