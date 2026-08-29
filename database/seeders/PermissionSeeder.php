<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'key' => 'users.manage',
                'name' => 'Administrar usuarios y permisos',
                'assignable_to_employee' => false,
            ],
            [
                'key' => 'categories.manage',
                'name' => 'Gestionar categorías',
                'assignable_to_employee' => true,
            ],
            [
                'key' => 'suppliers.manage',
                'name' => 'Gestionar proveedores',
                'assignable_to_employee' => true,
            ],
            [
                'key' => 'customers.manage',
                'name' => 'Gestionar clientes',
                'assignable_to_employee' => true,
            ],
            [
                'key' => 'products.manage',
                'name' => 'Gestionar productos',
                'assignable_to_employee' => true,
            ],
            [
                'key' => 'inventory.view',
                'name' => 'Consultar inventario',
                'assignable_to_employee' => true,
            ],
            [
                'key' => 'inventory.entries.create',
                'name' => 'Registrar entradas de stock',
                'assignable_to_employee' => true,
            ],
            [
                'key' => 'inventory.adjust',
                'name' => 'Realizar ajustes de inventario',
                'assignable_to_employee' => true,
            ],
            [
                'key' => 'sales.create',
                'name' => 'Registrar y confirmar ventas',
                'assignable_to_employee' => true,
            ],
            [
                'key' => 'sales.cancel',
                'name' => 'Anular ventas confirmadas',
                'assignable_to_employee' => true,
            ],
            [
                'key' => 'reports.view',
                'name' => 'Consultar reportes y kardex',
                'assignable_to_employee' => true,
            ],
            [
                'key' => 'reports.export',
                'name' => 'Exportar reportes a CSV',
                'assignable_to_employee' => true,
            ],
            [
                'key' => 'audit.view',
                'name' => 'Consultar registros de auditoría',
                'assignable_to_employee' => false,
            ],
        ];

        foreach ($permissions as $data) {
            Permission::updateOrCreate(
                ['key' => $data['key']],
                [
                    'name' => $data['name'],
                    'assignable_to_employee' => $data['assignable_to_employee'],
                ]
            );
        }
    }
}
