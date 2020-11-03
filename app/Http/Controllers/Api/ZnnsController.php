<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
/**
 * 宅男女神
 */
class ZnnsController extends CommonController{

    public function __construct()
    {   
        $this->webModel = New \App\Models\WebsiteModel();
        $web =  $this->webModel->find(3);
        $this->webUrl = $web->url;
        $this->start = $web->web_index;
        $this->model_url = 'g';

        $this->SetImgModel = New \App\Models\SetImgModel();

        $this->ImgsModel = New \App\Models\ImgsModel();
    }

    /**
     * 入口
     */
    public function scan(){
        
        
        
        

        for ($i= $this->start; $i <= 34170 ; $i++) { 
            $url = $this->webUrl . $this->model_url . '/' . $i;
            $pageData = $this->getPageData($url);
            $isPage = $this->checkPage($pageData);

            if($isPage){
                var_dump($pageData);exit;
                echo "123\r\n";
            }else{
                echo "未查询到该资源\r\n";
            }
            $this->webModel->where('id',3)->update(['web_index' => $i + 1]);
        }
        // var_dump($pageData);exit;
        // $this->getPageList($pageData);
        // echo $pageData;
    }

    /**
     * 爬取套图图片
     */
    public function spider(){
        $SetImgList = $this->SetImgModel->where('is_spider',0)->paginate(10);
        $total = $this->SetImgModel->where('is_spider',0)->count();
        $num = 0;
        while($SetImgList){
            foreach ($SetImgList as $key => $value) {
                $url = $this->webUrl . $value->url;
                $pageData = $this->getPageData($url,false,true);

                if($pageData){
                    $imgList = $this->getImgList($pageData);
                    $imgDataList = [];
                    foreach ($imgList as $imgKey => $imgValue) {
                        $temp['set_id'] =  $value->id;
                        $temp['url'] = $imgValue;
                        $temp['created_at'] = date("Y-m-d H:i:s",time());
                        $imgDataList[] = $temp;
                    }

                    DB::beginTransaction();
                    try{
                        $this->ImgsModel->insert($imgDataList);
                        $this->SetImgModel->where('id',$value->id)->update(['is_spider' => 1]);
            
                        DB::commit();
                    }catch (\Exception $e) {
                        var_dump($e->getMessage());exit;
                        DB::rollBack();
                    }
                }else{
                    continue;
                }
                $num++;
                echo $num . '/' . $total. "\r\n\r\n";
            }
            $SetImgList = $this->SetImgModel->where('is_spider',0)->paginate(10);
        }

        echo "爬取完毕\r\n\r\n";
    }

    /**
     * 爬取图片资源
     */
    public function img(){
        $imgList = $this->ImgsModel->where('is_spider',0)->paginate(10);

        while($imgList){
            foreach ($imgList as $key => $value) {
                $setImg = $this->SetImgModel->find($value->set_id);
                $pageData = $this->getPageData($value->url);

                if(strpos($pageData,'404') !== false){
                    $local_url = null;
                }else{
                    $local_url = $this->saveImg($setImg->name,$pageData);
                }

                $value->local_url = $local_url;
                $value->is_spider = 1;
                $value->save();
            }
            $imgList = $this->ImgsModel->where('is_spider',0)->paginate(10);
        }
    }

    /**
     * 处理首页信息获取最后一页
     */
    public function handlePageHome(){
        // $url = $this->webUrl . $this->model_url . '-1.html';
        
        // $pageData = $this->getPageData($url,false,true);

        // echo "首页数据爬去成功,开始解析页数\r\n";


        // $pageInfo = $this->pageRegex($pageData);
        // $lastPgae = $this->getLastPage($pageInfo);

        // echo "页数解析成功，共计:" . $lastPgae . "页,开始爬取数据\r\n\r\n";

        $count = $this->SetImgModel->count();

        $start = $count/40;

        $this->handleSetImg(1,782);
    }

    /**
     * 循环爬取套图信息
     */
    public function handleSetImg($start,$end){
        for ($i = $end; $i >= $start; $i--) { 
            $url = $this->webUrl . $this->model_url . '-'. $i .'.html';
            $pageData = $this->getPageData($url);
            $setImgList = $this->getSetImgList($pageData);
            $this->SetImgModel->insert($setImgList);

            echo "第$i/$end,页数据爬取成功\r\n\r\n";
            sleep(5);
        }
        echo "数据爬取完毕\r\n\r\n";
    }

    /**
     * 获取页面页码信息
     */
    public function pageRegex($str){
        $regex ="/<div class=\"pg\">.*?<\/div>/i";
        if(preg_match_all($regex, $str, $matches)){
            return $matches[0][0];
        }else{
            return false;
        }
    }

    /**
     * 检查该页面是否有内容
     */
    public function checkPage($str){
        if(strpos($str,'该页面未找到') !== false){
            return false;
        }else{
            return true;
        }
    }

    public function getPageList($str){
        $regex ="/<div id=\"pages\">(.*?)<\/div>/i";
        if(preg_match_all($regex, $str, $matches)){
            var_dump($matches);
            $pageRegex = "/<a href='(.*?)'.*?>(.*?)<\/a>/ism";
            if(preg_match_all($pageRegex, $matches[1][0], $pageMatches)){
                var_dump($pageMatches);exit;
            }else{
                return false;
            }
            
            return ;
        }else{
            return false;
        }
    }

    /**
     * 获取最后一页
     */
    public function getLastPage($str){
        $regex = "/<a .*? class=\"last\".*?>(.*?)<\/a>/i";
        if(preg_match_all($regex, $str , $matches)){
            return str_replace('... ','',$matches[1][0]);
        }else{
            return false;
        }
    }

    /**
     * 获取套图列表
     */
    public function getSetImgList($str){
        $regex = "/<th class=\"new\">(.*?)<\/th>/ism";
        $setImgList = [];
        if(preg_match_all($regex, $str , $matches)){

            $matches[0] = array_reverse($matches[0]);

            foreach ($matches[0] as $key => $value) {
                if(strpos($value,'公告') === false){
                    
                    $typeRegex = "/<em>.*?<a.*?>(.*?)<\/a>.*?<\/em>/ism";
                    preg_match_all($typeRegex, $value , $typeMatches);

                    $temp['type'] = $typeMatches[1][0];

                    $titleRegex = "/<a .*? class=\"s xst\">(.*?)<\/a>/ism";

                    preg_match_all($titleRegex, $value , $titleMatches);

                    $temp['name'] = $titleMatches[1][0];

                    $urlRegex = '/<\/em>.*?href=\"(.*?)\" onclick/ism';

                    preg_match_all($urlRegex, $value , $urlMatches);

                    $temp['url'] = str_replace('" style="font-weight: bold;color: #EE1B2E;','',$urlMatches[1][0]);

                    $temp['index'] = explode('-',$temp['url'])[1];

                    $temp['created_at'] = date("Y-m-d H:i:s");
                    $temp['last_scan_date'] = date("Y-m-d");

                    /**检查数据库是否有该套图 */
                    $is_spider = $this->SetImgModel->where('index',$temp['index'])->first();
                    if(!$is_spider){
                        $setImgList[] = $temp;
                    }
                }
            }
            return $setImgList;
        }else{
            var_dump($str);
            return false;
        }
        
    }

    public function getImgList($str){
        $regex = "/<ignore_js_op>(.*?)<\/ignore_js_op>/ism";

        preg_match_all($regex, $str , $matches);

        

        $imgList = [];
        foreach ($matches[0] as $key => $value) {

            if(strpos($value,'jpg') === false){
                continue;
            }

            if(strpos($value,'/static') !== false){
                $imgRegex = "/<img.*?file=\"(.*?)\".*?\/>/";
            }else{
                $imgRegex = "/<img.*?src=\"(.*?)\".*?\/>/";
            }

            preg_match_all($imgRegex, $value , $imgMatches);

            $imgList[] = $imgMatches[1][0];

        }

        return $imgList;
    }

    /*保存图片*/
    public function saveImg($dirName,$data = null){
        $saveDir =  base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'set_img' . DIRECTORY_SEPARATOR . $dirName;
        if(!is_dir($saveDir)){
            mkdir ($saveDir,0777,true);
        }
        $fileName = time() . '.png';
        $path = $saveDir . DIRECTORY_SEPARATOR . $fileName;

        $file = fopen($path,'w');
        fwrite($file,$data);
        fclose($file);
        $savePath = 'public' . DIRECTORY_SEPARATOR . 'set_img' . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR .$fileName;
        // return $path;
        // echo '接收文件'.$fileName;
        return $savePath;
    }

}