<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUrlTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('feed_id')->index();
            $table->string('uuid',32)->index();
            $table->string('url',191);
            $table->tinyInteger('mode');//0=list 1=content
            $table->tinyInteger('failed')->default(0); //失败次数
            $table->integer('reqtime')->default(0); //请求时长
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
        Schema::dropIfExists('tasks');
    }
}
