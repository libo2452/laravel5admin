<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSmzdmFeedTables extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create(
            'smzdm_feeds' ,
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('focus_pic');
                $table->string('link');
                $table->string('pubdate');
                $table->string('description');
                $table->text('content');
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('smzdm_feeds');
    }
}
