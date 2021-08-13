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
            $table->string('uuid',32)->index();
            $table->string('title',128)->default('')->nullable(); //标题
            $table->string('icon',128)->default('')->nullable(); //图标
            $table->string('description',128)->default('')->nullable(); //描述
            $table->unsignedInteger('update_wait')->default(0)->index(); //等待多少天更新
            $table->tinyInteger('state')->default(0)->comment('0失效 1正常');
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
