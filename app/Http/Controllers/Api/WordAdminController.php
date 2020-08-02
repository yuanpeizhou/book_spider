<?php
namespace App\Http\Controllers\Api;
use App\Exceptions\ParamsErrorException;

/*图片爬取处理*/
class WordAdminController extends CommonController
{   
    public $wordModel;

    public function __construct()
    {
        $this->wordModel = New \App\Models\WordModel();
    }

    public function wordList(){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 15;
        $keywoedImg = request()->keywordImg;
        $keywordWord = request()->keywordWord;

        $condition[] = ['id','>',0];

        // if($keywoedImg){
        //     $condition[] = ['']
        // }
        
        $res = $this->wordModel->where($condition)->paginate($limit);
    }

    public function wordInfo(){

    }

    public function wordUpdate(){

    }
}