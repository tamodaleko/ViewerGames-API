<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('game_id');
            $table->unsignedInteger('game_server_id');
            $table->unsignedInteger('game_skill_id')->nullable();
            $table->unsignedInteger('game_role_id')->nullable();
            $table->string('in_game_name');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('game_id')
                ->references('id')
                ->on('games')
                ->onDelete('restrict');

            $table->foreign('game_server_id')
                ->references('id')
                ->on('game_servers')
                ->onDelete('restrict');

            $table->foreign('game_skill_id')
                ->references('id')
                ->on('game_skills')
                ->onDelete('restrict');

            $table->foreign('game_role_id')
                ->references('id')
                ->on('game_roles')
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
        Schema::dropIfExists('player_cards');
    }
}
