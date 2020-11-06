<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->text('event_details');
            $table->bigInteger('user_id');
            $table->bigInteger('team_id');
            $table->string('event_organiser')->nullable();
            $table->timestamp('event_time');
            $table->string('event_title');
            $table->string('event_image')->nullable();
            $table->string('event_privacy')->default('PUBLIC');
            $table->integer('active')->default(1);
            $table->timestamp('event_deadline')->nullable();
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
        Schema::dropIfExists('events');
    }
}
