<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('student_nisn');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->date('date');
            $table->time('time_log');
            $table->enum('status', ['Hadir','Terlambat'])->default('Hadir');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('student_nisn');
            $table->index('device_id');
            $table->index('schedule_id');

            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->foreign('student_nisn')->references('nisn')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_logs');
    }
};
