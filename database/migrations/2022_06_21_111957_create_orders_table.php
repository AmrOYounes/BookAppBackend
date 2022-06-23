<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Book_id');
            $table->foreign('Book_id')->references('Book_id')->on('books');
            $table->string('numberOfUnits');
            $table->string('buyerName');
            $table->string('buyerAdress');
            $table->string('phone');
            $table->date('purchaseDate');
            $table->string('nationalId');
            $table->string('paymentMethod');
            $table->string('totalPrice');
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
        Schema::dropIfExists('orders');
    }
};
