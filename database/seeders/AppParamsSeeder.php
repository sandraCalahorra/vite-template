<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class AppParamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $params = [
            (object)['id' => '1', 'version' => '1.0.0', 'created_at' => now(), 'updated_at' => now()],
        ];

        Schema::disableForeignKeyConstraints();

        $maintenance = DB::table('app_params')->value('maintenance') ?? 0;

        DB::table('app_params')->truncate();

        foreach ($params as $key => $params) {
            DB::table('app_params')->insert(
                [
                    'id' => $params->id,
                    'version' => $params->version,
                    'created_at' => $params->created_at,
                    'updated_at' => $params->updated_at,
                    'maintenance' => $maintenance,
                ]
            );
        }

        Schema::enableForeignKeyConstraints();
    }
}


