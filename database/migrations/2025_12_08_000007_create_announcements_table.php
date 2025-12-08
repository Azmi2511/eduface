<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->increments('id');
            $table->text('message')->nullable();
            $table->string('attachment_file', 255)->nullable();
            $table->text('attachment_link')->nullable();
            $table->dateTime('datetime_send')->nullable();
            $table->text('recipient')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('update_at')->nullable();
            // note: original dump indexed recipient(text) with prefix; skipping text index here
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
    }
};
