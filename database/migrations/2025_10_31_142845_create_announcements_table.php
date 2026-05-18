<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content');
            $table->boolean('is_active')->default(0);
            $table->boolean('is_blocking')->default(false);
            $table->datetime('expires_on')->nullable();
            $table->timestamps();
        });

        DB::unprepared('
            CREATE EVENT IF NOT EXISTS deactivate_expired_announcements
            ON SCHEDULE EVERY 1 HOUR
            DO
            UPDATE announcements
            SET is_active = 0
            WHERE (
                expires_on IS NOT NULL
                AND expires_on < NOW()
            );
        ');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');

        DB::unprepared('DROP EVENT IF EXISTS deactivate_expired_announcements');
    }
};
