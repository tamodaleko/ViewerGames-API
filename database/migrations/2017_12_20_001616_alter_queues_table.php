<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->unsignedInteger('game_map_id')->nullable()->change();
            $table->unsignedInteger('game_skill_id')->nullable()->change();
            $table->unsignedInteger('queue_status_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->unsignedInteger('game_map_id')->change();
            $table->unsignedInteger('game_skill_id')->change();
            $table->unsignedInteger('queue_status_id')->change();
        });
    }
}
