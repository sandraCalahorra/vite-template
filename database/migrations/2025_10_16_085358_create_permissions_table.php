<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions_actions_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->string('entity');
            $table->string('level');
            $table->foreign('level')->references('level')->on('roles')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('action_id')->constrained('permissions_actions_types')->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['entity', 'level', 'action_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('permissions_actions_types');
    }
};
