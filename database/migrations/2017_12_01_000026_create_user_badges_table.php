<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_badges', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('receiver_user_id');
            $table->unsignedInteger('issuer_user_id');
            $table->unsignedInteger('badge_id');
            $table->unsignedInteger('match_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('receiver_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('issuer_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('badge_id')
                ->references('id')
                ->on('badges')
                ->onDelete('cascade');

            $table->foreign('match_id')
                ->references('id')
                ->on('matches')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_badges');
    }
}
