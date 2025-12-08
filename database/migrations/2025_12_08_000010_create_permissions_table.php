<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('student_nisn');
            $table->unsignedBigInteger('parent_id');
            $table->enum('type', ['Sakit','Izin']);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->string('proof_file_path', 255)->nullable();
            $table->enum('approval_status', ['Pending','Approved','Rejected'])->default('Pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('student_nisn');
            $table->index('parent_id');
            $table->index('approved_by');

            $table->foreign('parent_id')->references('id')->on('parents')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('student_nisn')->references('nisn')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
};
