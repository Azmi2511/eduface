<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->decimal('school_latitude', 10, 8)->nullable();
            $table->decimal('school_longitude', 11, 8)->nullable();
            $table->integer('allowed_radius_meters')->default(100); // radius default 100 meter
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'school_latitude',
                'school_longitude',
                'allowed_radius_meters'
            ]);
        });
    }
};
