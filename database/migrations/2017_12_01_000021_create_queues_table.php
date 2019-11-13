<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('game_id');
            $table->unsignedInteger('game_map_id');
            $table->unsignedInteger('game_server_id');
            $table->unsignedInteger('game_skill_id');
            $table->unsignedInteger('queue_status_id');
            $table->boolean('streamer_mode');
            $table->integer('team_count');
            $table->integer('players_per_team');
            $table->timestamp('start_time')->nullable();
            $table->decimal('ticket_cost', 10, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('game_id')
                ->references('id')
                ->on('games')
                ->onDelete('cascade');

            $table->foreign('game_map_id')
                ->references('id')
                ->on('game_maps')
                ->onDelete('restrict');

            $table->foreign('game_server_id')
                ->references('id')
                ->on('game_servers')
                ->onDelete('restrict');

            $table->foreign('game_skill_id')
                ->references('id')
                ->on('game_skills')
                ->onDelete('restrict');

            $table->foreign('queue_status_id')
                ->references('id')
                ->on('queue_statuses')
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
        Schema::dropIfExists('queues');
    }
}
