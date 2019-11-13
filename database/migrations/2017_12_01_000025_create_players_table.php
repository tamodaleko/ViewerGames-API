<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('team_id');
            $table->unsignedInteger('skill_id');
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('queue_id');
            $table->integer('position_in_queue');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('cascade');

            $table->foreign('skill_id')
                ->references('id')
                ->on('game_skills')
                ->onDelete('restrict');

            $table->foreign('role_id')
                ->references('id')
                ->on('game_roles')
                ->onDelete('restrict');

            $table->foreign('queue_id')
                ->references('id')
                ->on('queues')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('players');
    }
}
