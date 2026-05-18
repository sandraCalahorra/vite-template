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
        DB::table('roles')->insert(
            [
            'name' => 'User',
            'level' => '20',
            ]
        );
        DB::table('roles')->insert(
            [
            'name' => 'Administrador',
            'level' => '10',
            ]
        );
        DB::table('roles')->insert(
            [
            'name' => 'Superadministrador',
            'level' => '0',
            ]
        );
        DB::table('users')->insert(
            [
            'name' => 'Superadministrador',
            'email' => 'superadmin@ecomputer.es',
            'password' => Hash::make('1234'),
            'level' => '0',
            ]
        );
        DB::table('users')->insert(
            [
            'name' => 'Administrador',
            'email' => 'admin@ecomputer.es',
            'password' => Hash::make('1234'),
            'level' => '10',
            ]
        );
        DB::table('users')->insert(
            [
            'name' => 'Usuario',
            'email' => 'user@ecomputer.es',
            'password' => Hash::make('1234'),
            'level' => '20',
            ]
        );
    }
}
