<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangelogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $changelogs = [

            (object)[
                'id' => '1',
                'version' => '1.0.0',
                'news' => '<p><strong>Bienvenido a la nueva versión de la plataforma</strong></p>',
                'improvements' => NULL,
                'bugfixes' => NULL,
                'created_at' => '2024-10-30 00:00:00',
                'updated_at' => NULL,
            ]

        ];

        Schema::disableForeignKeyConstraints();
        DB::table('changelog')->truncate();

        foreach ($changelogs as $changelog) {
            DB::table('changelog')->insert([
                'version' => $changelog->version,
                'news' => $changelog->news,
                'improvements' => $changelog->improvements,
                'bugfixes' => $changelog->bugfixes,
                'created_at' => $changelog->created_at,
                'updated_at' => $changelog->updated_at,
            ]);
        }

        Schema::enableForeignKeyConstraints();
    }
}
