<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('event_id');
            $table->text('project_name');
            $table->integer('slot');
            $table->integer('foreman_id');
            $table->json('staff_id')->nullable();
            $table->text('notes')->nullable();
            $table->text('start');
            $table->text('end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_schedules');
    }
};
