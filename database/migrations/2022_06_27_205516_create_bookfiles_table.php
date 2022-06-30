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
        Schema::create('bookfiles', function (Blueprint $table) {
            $table->id();
            $table->string('Book_path');
            $table->unsignedBigInteger('Book_id');
            $table->foreign('Book_id')->references('Book_id')->on('books');
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
        Schema::dropIfExists('bookfiles');
    }
};
