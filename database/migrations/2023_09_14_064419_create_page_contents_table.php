<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageContentsTable extends Migration
{
    public function up()
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->string('title');
            $table->text('description');
            $table->json('content_photos')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();

            // Define foreign key constraint for page_id
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('page_contents');
    }
}