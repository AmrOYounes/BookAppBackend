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
        Schema::create('books', function (Blueprint $table) {
            $table->id('Book_id');
            $table->string('Book_title');
            $table->unsignedBigInteger('publisher_Id');
            $table->foreign('publisher_Id')->references('id')->on('publishers');
            $table->unsignedBigInteger('author_Id');
            $table->foreign('author_Id')->references('id')->on('authors');
            $table->date('Publish_date');
//            $table->string('Book_author');
//            $table->string('Book_path');
            $table->integer('Available_units');
            $table->integer('Unit_price');
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
        Schema::dropIfExists('books');
    }
};
