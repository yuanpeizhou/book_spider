<?php

namespace App\Models;

class BookModel extends BaseModel {

    const NOT_UPDATE = 1; // 未更新
    const UPDATED = 2; // 已更新
    CONST WAIT_SIPDER = 3; // 等待抓取

    protected $table = 'book';

    public function conChapter(){
        return $this->hasMany(ChapterModel::class,'book_id','id');
    }
}