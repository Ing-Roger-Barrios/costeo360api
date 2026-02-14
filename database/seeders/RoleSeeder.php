<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrador',
                'description' => 'Acceso total al sistema'
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrador',
                'description' => 'Gestión completa de usuarios y contenido'
            ],
            [
                'name' => 'editor',
                'display_name' => 'Editor',
                'description' => 'Puede crear y editar contenido'
            ],
            [
                'name' => 'user',
                'display_name' => 'Usuario',
                'description' => 'Acceso básico al sistema'
            ]
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
