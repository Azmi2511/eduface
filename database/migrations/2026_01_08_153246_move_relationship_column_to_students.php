<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->enum('relationship', ['Ayah', 'Ibu', 'Wali'])
                  ->nullable()
                  ->after('parent_id');
        });
        if (Schema::hasColumn('parents', 'relationship')) {
            DB::statement("UPDATE students 
                           SET relationship = (
                               SELECT CASE 
                                   WHEN p.relationship LIKE '%Ayah%' THEN 'Ayah'
                                   WHEN p.relationship LIKE '%Ibu%' THEN 'Ibu'
                                   ELSE 'Wali'
                               END 
                               FROM parents p 
                               WHERE p.id = students.parent_id
                           ) 
                           WHERE parent_id IS NOT NULL");

            Schema::table('parents', function (Blueprint $table) {
                $table->dropColumn('relationship');
            });
        }
    }

    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->string('relationship')->nullable();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('relationship');
        });
    }
};