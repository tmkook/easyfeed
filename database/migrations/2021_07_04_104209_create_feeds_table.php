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
            $table->string('url');
            $table->string('title')->nullable();
            $table->string('icon')->nullable();
            $table->string('description')->nullable();
            $table->string('list_dom')->nullable();
            $table->string('main_dom')->nullable();
            $table->string('comment_num_dom')->nullable();
            $table->tinyInteger('state')->default(0)->comment('0审核 1正常 2失效');
            $table->timestamps();
            //$table->softDeletes();

            // $table->string('phone');
            // $table->tinyInteger('vip_level')->default(0);
            // $table->integer('free_feed')->default(0);
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
