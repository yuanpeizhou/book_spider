<?php
namespace App\Http\Controllers\Api;
use App\Exceptions\ParamsErrorException;

/*图片爬取处理*/
class WordAdminController extends CommonController
{   
    public $wordModel;

    public $localUrl = 'http://127.0.0.1/book_spider/';

    public $originUrl;

    public function __construct()
    {
        $this->wordModel = New \App\Models\WordModel();

        $webModel = New \App\Models\WebsiteModel();
        
        $this->originUrl =  $webModel->find(1)->url;
    }

    public function wordList(){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 15;
        $keywoedImg = request()->keywordImg;
        $keywordWord = request()->keywordWord;

        $condition[] = ['id','>',0];


        if($keywoedImg){
            $condition[] = ['local_url','like',"%$keywoedImg%"];
        }

        if($keywordWord){
            $condition[] = ['word','like',"%$keywordWord%"];
        }
        
        $res = $this->wordModel->where($condition)->groupBy('md5')->orderBy('id','desc')->paginate($limit);

        foreach ($res as $key => $value) {
            $res[$key]->origin_url = $this->originUrl . $value->origin_url;
            $res[$key]->local_url = $this->localUrl . $value->local_url;
        }

        return $this->returnApi(200,'ok',$res->toArray());
    }

    public function wordUpdate(){
        $id = request()->id;
        $word = request()->word;

        $wordRes = $this->wordModel->find($id);

        if(!$wordRes){
            return $this->returnApi(201,'参数传递错误','');
        }

        $wordRes->word = $word;
        $res = $wordRes->save();

        if(!$res){
            return $this->returnApi(202,'保存失败');
        }else{
            return $this->returnApi(200,'ok');
        }
    }

    public function wordInfo(){
    }
}