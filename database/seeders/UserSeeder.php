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
            'name' => 'Familiar',
            'level' => '30',
            ]
        );
        DB::table('roles')->insert(
            [
            'name' => 'Estudiante',
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
            'name' => 'Sandra Calahorra',
            'email' => 'sandracalahorra@ecomputer.es',
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
            'name' => 'Estudiante',
            'email' => 'estudiante@ecomputer.es',
            'password' => Hash::make('1234'),
            'level' => '20',
            ]
        );
        DB::table('users')->insert(
            [
            'name' => 'Familiar',
            'email' => 'familiar@ecomputer.es',
            'password' => Hash::make('1234'),
            'level' => '30',
            ]
        );
    }
}
