<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'administrador']);
        Role::firstOrCreate(['name' => 'usuario']);
        Role::firstOrCreate(['name' => 'jefe']);
        Role::firstOrCreate(['name' => 'administrador_sgi']);
    }
}
