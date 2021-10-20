<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title',100)->comment('文章标题');
            $table->string('cover',150)->nullable()->comemnt('文章封面配图');
            $table->text('content')->comment('文章具体内容');
            $table->integer('user_id')->default(0)->commnet('文章作者');
            $table->integer('views')->default(0)->comment('浏览量');
            $table->integer('praises')->default(0)->comment('点赞量');
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
        Schema::create('articles', function (Blueprint $table) {

        });
    }
}
