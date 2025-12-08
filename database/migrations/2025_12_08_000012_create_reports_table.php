<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('report_name');
            $table->string('report_type',50);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->enum('status', ['Selesai','Pending','Gagal'])->default('Pending');
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
