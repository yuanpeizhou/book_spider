<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
/**
 * 套图类
 */
class SetImgController extends CommonController{

    public function __construct()
    {   
        $webModel = New \App\Models\WebsiteModel();
        $web =  $webModel->find(2);
        $this->webUrl = $web->url;
        $this->model_url = 'forum-155';
    }

    public function scan(){
        $this->handlePageHome();
    }

    /**
     * 
     */
    public function handlePageHome(){
        $url = $this->webUrl . $this->model_url . '-1.html';

        // var_dump($url);exit;

        $pageData = $this->getPageData($url);

        // echo $pageData;exit;

        $file = fopen('text.html','a');
        fwrite($file,$pageData);
        fclose($file);

        // var_dump($pageData);exit;

        // return $this->returnApi(200,'ok',$pageData);

        // $this->pageRegex($pageData);

        // var_dump($pageData);exit;
    }

    /**
     * 获取页面页码信息
     */
    public function pageRegex($str){
        $regex ="/<span id=\"fd_page_bottom\">.*?<\/span>/i";
        if(preg_match_all($regex, $str, $matches)){
            // return true;
            var_dump('123');exit;
            var_dump($matches);exit;
        }else{
            // return false;]
            var_dump('12');
        }
    }
}