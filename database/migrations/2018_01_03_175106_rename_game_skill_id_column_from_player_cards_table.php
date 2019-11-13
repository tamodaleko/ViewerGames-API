<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameGameSkillIdColumnFromPlayerCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('player_cards', function (Blueprint $table) {
            $table->renameColumn('game_skill_id', 'game_rank_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('player_cards', function (Blueprint $table) {
            $table->renameColumn('game_rank_id', 'game_skill_id');
        });
    }
}
