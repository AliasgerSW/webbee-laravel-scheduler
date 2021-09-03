<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SchedulerTestDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schedule Master Table
        Schema::create('schedules', function($table) {
            $table->increments('id');
            $table->dateTime('date_from');
            $table->dateTime('date_to');
            $table->integer('slot_duration');
            $table->integer('book_before');
            $table->timestamps();
        });

        //Registered Slots of the Schedules
        Schema::create('schedule_slots', function($table) {
            $table->increments('id');
            $table->integer('schedule_id')->unsigned();
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->dateTime('slot_time');
            $table->integer('available')->unsigned();
            $table->integer('booked')->unsigned();
            $table->timestamps();
        });

        // Entry of the booking will be made here
        Schema::create('schedule_booking', function($table) {
            $table->bigIncrements('id');
            $table->integer('schedule_id')->unsigned();
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->integer('slot_id')->unsigned();
            $table->foreign('slot_id')->references('id')->on('schedule_slots')->onDelete('cascade');            
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->tinyInteger('qty');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('schedule_slots');
        Schema::dropIfExists('schedule_booking');
    }
}
