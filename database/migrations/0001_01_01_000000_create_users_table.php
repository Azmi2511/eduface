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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 100)->unique();
            $table->string('email', 100)->unique()->nullable();
            $table->string('password');
            $table->string('full_name');
            $table->string('phone', 50)->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['L','P'])->nullable();
            $table->string('profile_picture')->nullable();
            $table->enum('role', ['admin','teacher','student','parent']);
            $table->boolean('is_active')->default(1);
            $table->rememberToken();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
