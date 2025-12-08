<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('nisn')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('full_name');
            $table->enum('gender', ['L','P']);
            $table->string('photo_path')->nullable();
            $table->tinyInteger('is_face_registered')->default(0);
            $table->timestamp('face_registered_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('user_id');
            $table->index('class_id');
            $table->index('parent_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('set null');
            $table->foreign('parent_id')->references('id')->on('parents')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
};
