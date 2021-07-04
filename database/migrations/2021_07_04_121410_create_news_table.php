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
            $table->string('title',128);
            $table->string('summary',128)->nullable();
            $table->string('cover',128)->default('')->nullable();
            $table->longText('main')->nullable();
            $table->unsignedInteger('comment_count')->default(0); //评论数
            $table->unsignedInteger('view_count')->default(0); //阅读数
            $table->unsignedInteger('share_count')->default(0); //分享数
            $table->unsignedInteger('jump_count')->default(0); //查看源站数
            $table->tinyInteger('state')->default(0)->comment('-1失败 0添加 1正常');
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
