<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

public function run()
{
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'inspector']);
    Role::create(['name' => 'user']);

}

}
