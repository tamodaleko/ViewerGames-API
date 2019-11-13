<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('provider_id');
            $table->string('provider_user_id', 255);
            $table->string('username', 255)->nullable();
            $table->string('name', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->timestamps();

            $table->unique(['provider_id', 'provider_user_id']);

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
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
        Schema::dropIfExists('users');
    }
}
