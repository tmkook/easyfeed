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
            $table->string('title',128);
            $table->string('summary',128);
            $table->string('cover',128)->default('')->nullable();
            $table->text('main');
            $table->string('url',128);
            $table->integer('comment_count')->default(0); //评论数
            $table->integer('view_count')->default(0); //阅读数
            $table->integer('share_count')->default(0); //分享数
            $table->integer('jump_count')->default(0); //查看源站数
            $table->tinyInteger('state')->default(0)->comment('-1失败 0添加 1正常');
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
        Schema::dropIfExists('news');
    }
}
