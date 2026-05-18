<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class PermissionsActionsTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $params = [
            (object)['id' => '1', 'name' => 'view', 'color' => '#9acdfa', 'created_at' => now(), 'updated_at' => now()],
            (object)['id' => '2', 'name' => 'create', 'color' => '#a4dea9', 'created_at' => now(), 'updated_at' => now()],
            (object)['id' => '3', 'name' => 'edit', 'color' => '#edd786', 'created_at' => now(), 'updated_at' => now()],
            (object)['id' => '4', 'name' => 'delete', 'color' => '#ff7f7f', 'created_at' => now(), 'updated_at' => now()],
        ];

        Schema::disableForeignKeyConstraints();

        DB::table('permissions_actions_types')->truncate();

        foreach ($params as $key => $params) {
            DB::table('permissions_actions_types')->insert(
                [
                'id' => $params->id,
                'name' => $params->name,
                'color' => $params->color,
                'created_at' => $params->created_at,
                'updated_at' => $params->updated_at
                ]
            );
        }

        Schema::enableForeignKeyConstraints();
    }
}


