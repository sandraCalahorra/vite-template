<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $params = [
            (object)['entity' => 'users', 'level' => '10', 'action_id' => '1', 'created_at' => now(), 'updated_at' => now()],
            (object)['entity' => 'users', 'level' => '10', 'action_id' => '2', 'created_at' => now(), 'updated_at' => now()],
            (object)['entity' => 'users', 'level' => '10', 'action_id' => '3', 'created_at' => now(), 'updated_at' => now()],
            (object)['entity' => 'users', 'level' => '10', 'action_id' => '4', 'created_at' => now(), 'updated_at' => now()],

            (object)['entity' => 'my_profile', 'level' => '10', 'action_id' => '1', 'created_at' => now(), 'updated_at' => now()],
            (object)['entity' => 'my_profile', 'level' => '10', 'action_id' => '2', 'created_at' => now(), 'updated_at' => now()],
            (object)['entity' => 'my_profile', 'level' => '10', 'action_id' => '3', 'created_at' => now(), 'updated_at' => now()],
            (object)['entity' => 'my_profile', 'level' => '20', 'action_id' => '1', 'created_at' => now(), 'updated_at' => now()],
            (object)['entity' => 'my_profile', 'level' => '20', 'action_id' => '2', 'created_at' => now(), 'updated_at' => now()],
            (object)['entity' => 'my_profile', 'level' => '20', 'action_id' => '3', 'created_at' => now(), 'updated_at' => now()],

            (object)['entity' => 'tickets', 'level' => '10', 'action_id' => '1', 'created_at' => now(), 'updated_at' => now()],
            (object)['entity' => 'tickets', 'level' => '10', 'action_id' => '2', 'created_at' => now(), 'updated_at' => now()],

        ];

        Schema::disableForeignKeyConstraints();

        DB::table('permissions')->truncate();

        foreach ($params as $key => $params) {
            DB::table('permissions')->insert(
                [
                'entity' => $params->entity,
                'level' => $params->level,
                'action_id' => $params->action_id,
                'created_at' => $params->created_at,
                'updated_at' => $params->updated_at
                ]
            );
        }

        Schema::enableForeignKeyConstraints();
    }
}


