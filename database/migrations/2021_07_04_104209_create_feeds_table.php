<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->string('url',128);
            $table->string('title',128)->nullable(); //标题
            $table->string('icon',128)->nullable(); //图标
            $table->string('description',128)->nullable(); //描述
            $table->string('list_dom',128)->nullable(); //列表
            $table->string('main_dom',128)->nullable(); //正文
            $table->string('next_dom',128)->nullable(); //下一页按钮
            $table->unsignedInteger('news_count')->default(0); //更新文章
            $table->unsignedInteger('feed_count')->default(0); //订阅人数
            $table->unsignedInteger('update_next')->default(0)->index(); //下一次更新日期
            $table->tinyInteger('category_id')->default(0); //所属分类
            $table->tinyInteger('state')->default(0)->comment('-1不支持 0审核 1正常 2失效');
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
        Schema::dropIfExists('feeds');
    }
}
