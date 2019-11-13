<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('paid_user_id');
            $table->unsignedInteger('payout_method_id');
            $table->decimal('payout_amount', 10, 2);
            $table->string('email_address', 255);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('paid_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('payout_method_id')
                ->references('id')
                ->on('payout_methods')
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
        Schema::dropIfExists('payouts');
    }
}
