<?php

namespace App\Models;

class BookModel extends BaseModel {

    protected $table = 'book';

    public function conChapter(){
        return $this->hasMany(ChapterModel::class,'book_id','id');
    }

}