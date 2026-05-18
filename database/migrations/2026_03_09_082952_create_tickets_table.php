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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('pending');
            $table->string('priority')->default('normal');
            $table->boolean('email_sent')->default(true);
            $table->timestamps();
        });

        // Add ticket_id to files table for relationship
        if (Schema::hasTable('files')) {
            Schema::table('files', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
                $table->foreignId('ticket_id')->nullable()->after('user_id')->constrained('tickets')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('files')) {
            Schema::table('files', function (Blueprint $table) {
                $table->dropForeign(['ticket_id']);
                $table->dropColumn('ticket_id');
            });
        }
        Schema::dropIfExists('tickets');
    }
};
