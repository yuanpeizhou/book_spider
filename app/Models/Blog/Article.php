<?php
namespace App\Models\Blog;

use App\Models\BaseModel;
use App\User;

/**
 * 博客文章model
 */
class Article extends BaseModel{

    /**通过反射方式获取常量 */
    static function getConstants() {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    protected $table = 'articles';

    //关联作者
    public function user(){
        return $this->belongsTo(User::class);
    }

}