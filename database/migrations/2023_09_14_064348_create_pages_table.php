<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->integer('serial')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();

            // Define foreign key constraint for parent_id
            $table->foreign('parent_id')->references('id')->on('pages')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pages');
    }
}