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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'is_face_registered')) {
                $table->boolean('is_face_registered')->default(0);
            }
            if (!Schema::hasColumn('students', 'photo_path')) {
                $table->string('photo_path')->nullable();
            }
            if (!Schema::hasColumn('students', 'face_registered_at')) {
                $table->timestamp('face_registered_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            //
        });
    }
};
