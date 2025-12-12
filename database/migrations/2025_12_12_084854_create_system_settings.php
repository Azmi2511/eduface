<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->integer('id')->default(1)->primary();
            $table->string('school_name', 100)->nullable();
            $table->string('npsn', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('language', 50)->default('Bahasa Indonesia');
            $table->string('timezone', 50)->default('WIB (UTC + 7)');
            $table->time('entry_time')->default('07:00:00');
            $table->time('late_limit')->default('07:30:00');
            $table->time('exit_time')->default('15:00:00');
            $table->integer('tolerance_minutes')->default(15);
            $table->boolean('face_rec_enabled')->default(1);
            $table->integer('min_accuracy')->default(85);
            $table->boolean('notif_late')->default(1);
            $table->boolean('notif_absent')->default(1);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
