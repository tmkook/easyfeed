<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('feed_id')->index();
            $table->string('uuid',32)->index();
            $table->string('url',191);
            $table->string('title',191);
            $table->text('cover',128)->nullable();
            $table->string('summary',128)->default('')->nullable();
            $table->longText('content')->nullable();
            $table->integer('weight')->default(0); //质量权重
            $table->integer('state')->default(1); //0无效 1正常
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news');
    }
}
