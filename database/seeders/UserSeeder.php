<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $password = Hash::make('password123');

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Administrador', 'email' => 'admin@turismo.test', 'password' => $password, 'phone' => '7000-0001', 'role' => 'admin', 'traveler_type' => null, 'status' => 'activo', 'country_of_origin' => 'El Salvador', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Carlos Mendoza', 'email' => 'carlos@test.com', 'password' => $password, 'phone' => '7000-0002', 'role' => 'user', 'traveler_type' => 'mochilero', 'status' => 'activo', 'country_of_origin' => 'El Salvador', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'María López', 'email' => 'maria@test.com', 'password' => $password, 'phone' => '7000-0003', 'role' => 'user', 'traveler_type' => 'familia', 'status' => 'activo', 'country_of_origin' => 'Guatemala', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'John Smith', 'email' => 'john@test.com', 'password' => $password, 'phone' => null, 'role' => 'user', 'traveler_type' => 'pareja', 'status' => 'activo', 'country_of_origin' => 'Estados Unidos', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
