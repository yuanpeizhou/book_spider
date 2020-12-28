<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
/**
 * 宅男女神
 */
class AotubaController extends CommonController{

    public function __construct()
    {   
        $this->webModel = New \App\Models\WebsiteModel();
        $web =  $this->webModel->find(5);
        $this->webUrl = $web->url;
        $this->start = $web->web_index;
        $this->model_url = 'post';

        $this->aotubaModel = New \App\Models\AotubaModel();

        $this->aotubaImgModel = New \App\Models\AotubaImgModel();

        $this->set_img_is_true = true;
    }

    /**
     * 入口
     */
    public function scan(){

        // $url= 'https://www.tuao8.cc/zb_users/upload/2020/11/202011161605513275401539.jpg';
        // $pageData = $this->httpRequest($url);
        // echo $pageData;
        // exit;
        echo "开始爬取\r\n";
        for ($i = $this->start; $i >= 1; $i--) { 
            /**
             * 检查套图信息是否爬取完毕
             */
            $setImg = $this->aotubaModel->where('index',$i)->first();
            if($setImg && $setImg->is_spider == 1){
                continue;
            }

            $temp = [];
            $url = $this->webUrl . $this->model_url . '/' . $i . '.html';
            $pageData = $this->httpRequest($url);
            // echo $pageData;exit;
            $isPage = $this->checkPage($pageData);

            if($isPage){
                $title = $this->getTitle($pageData);
                // var_dump($title);exit;

                if(!$setImg){
                    $temp['name'] = $title ? str_replace(' ','',$title) : null;
                    $temp['url'] = $url;
                    $temp['index'] = $i;
                    $temp['created_at'] = date("Y-m-d H:i:s");
                    $setImgId = $this->aotubaModel->insertGetId($temp);
                }else{
                    $setImgId = $setImg->id;
                }

                echo "套图《".$title."》信息保存成功,开始爬取图片\r\n";

                $this->imgScan($setImgId,$pageData,$url,$title);

                $this->aotubaModel->where('id',$setImgId)->update(['is_spider' => $this->set_img_is_true ? 1 : 0 , 'number' => $this->aotubaImgModel->where('aotuba_img_id',$setImgId)->count()]);

                echo "套图《".$title."》爬取成功\r\n\r\n";

            }else{
                echo "未查询到该资源,跳过\r\n\r\n";
            }

            $this->webModel->where('id',5)->update(['web_index' => $i - 1]);
            $this->set_img_is_true = true;
        }
    }

    /**
     * 扫描图片
     */
    public function imgScan($setImgId,$pageData,$url,$title){
        $imgList = [];
        $isEnd = false;

        /**获取第一页的图片 */
        $img = $this->getImgList($pageData);

        if($img){
            $is_spider = $this->aotubaImgModel->where('aotuba_img_id',$setImgId)->where('order',1)->first();

            if(!$is_spider){
                $this->spiderImg($setImgId,$img,1,$title);
            }

            if($is_spider && $is_spider->is_spider == 0){
                $this->spiderImg($setImgId,$img,1,$title,true,$is_spider);
            }
        }
        
        /**获取当前页面的最后一页 */
        $lastPage = $this->lastPageRegex($pageData);

        for ($i= 2; $i <= $lastPage; $i++) { 
            $is_spider = $this->aotubaImgModel->where('aotuba_img_id',$setImgId)->where('order',$i)->first();

            if($is_spider && $is_spider->is_spider == 1){
                echo "图片".$is_spider->order."已入库\r\n";
                continue;
            }

            $httpUrl = $url . '?page=' . $i;
            $pageData = $this->httpRequest($httpUrl);
            $img = $this->getImgList($pageData);

            if($is_spider && $is_spider->is_spider == 0){
                $this->spiderImg($setImgId,$img,$i,$title,true,$is_spider);
            }else{
                $this->spiderImg($setImgId,$img,$i,$title);
            }
            
        }
    }

    /**爬取图片资源并保存 */
    public function spiderImg($setImgId,$url,$name,$title,$is_update = false,$img = null){

        if($is_update){
            $imgFile = $this->httpRequest($url);
            if($imgFile){
                $img->origin_url = $url;
                $img->local_url = $this->saveImg($title,$imgFile,$name);
                $img->is_spider = 1;
                $img->save();
                echo "图片".$img->order."爬取成功\r\n";
            }
        }else{
            $imgTemp = [];
            $imgTemp['aotuba_img_id'] = $setImgId;
            $imgTemp['origin_url'] = $url;
            $imgTemp['created_at'] = date("Y-m-d H:i:s");
            $imgTemp['order'] = $name;
    
            $imgFile = $this->httpRequest($url);
            if($imgFile){
                $imgTemp['local_url'] = $this->saveImg($title,$imgFile,$name);
                $imgTemp['is_spider'] = 1;
                echo "图片".$imgTemp['local_url']."爬取成功\r\n";
            }
            $this->aotubaImgModel->insert($imgTemp);
        }     
    }

    /**
     * 获取套图名称
     */
    public function getTitle($str){
        $regex ="/<h1 class=\"title\">(.*?)<\/h1>/i";
        if(preg_match_all($regex, $str, $matches)){
            if(isset($matches[1][0])){
                return $matches[1][0];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 获取页面页码信息
     */
    public function lastPageRegex($str){
        $regex ="/<li><a href=\".*?\">(.*?)<\/a><\/li>/i";
        if(preg_match_all($regex, $str, $matches)){
            array_pop($matches[1]);
            return array_pop($matches[1]);
        }else{
            return false;
        }
    }

    /**
     * 检查该页面是否有内容
     */
    public function checkPage($str){
        if(strpos($str,'页面不存在') !== false){
            return false;
        }else{
            return true;
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
     * 获取页面图片列表
     */
    public function getImgList($str){
        $regex = "/<img title=\".*?\" src=\"(.*?)\".*?\/>/ism";
        if(preg_match_all($regex, $str , $matches)){
            return $matches[1][0];
        }else{  
            return null;
        }
    }

    /*保存图片*/
    public function saveImg($dirName,$data = null,$fileName){
        $saveDir =  base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'aotuba_img' . DIRECTORY_SEPARATOR . $dirName;
        if(!is_dir($saveDir)){
            try {
                mkdir ($saveDir,0777,true);
            } catch (\Throwable $th) {
                $saveDir = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'aotuba_img' . DIRECTORY_SEPARATOR . '失败';
            }
        }
        $fileName = $fileName . '.png';
        $path = $saveDir . DIRECTORY_SEPARATOR . $fileName;

        $file = fopen($path,'w');
        fwrite($file,$data);
        fclose($file);
        $savePath = 'public' . DIRECTORY_SEPARATOR . 'aotuba_img' . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR .$fileName;
        // return $path;
        // echo '接收文件'.$fileName;
        return $savePath;
    }
    
    /**
     * 爬取请求(最多重复请求三次)
     * 
     */
    public function httpRequest($url){
        ini_set('memory_limit', '256M');
        $res = $this->httpGet($url);

        if(!$res){
            echo "页面请求失败，延时30秒尝试第二次请求\r\n";
            sleep(30);
            $res = $this->httpGet($url);
        }

        if(!$res){
            echo "页面请求失败，延时60秒尝试第三次请求\r\n";
            sleep(60);
            $res = $this->httpGet($url);
        }

        if(!$res){
            echo "页面请求失败,跳过该页面\r\n";
            $this->set_img_is_true = false;
            return false;
        }

        $code = mb_detect_encoding($res, array('GB2312','UTF-8', 'GBK'));

        if($code == 'EUC-CN' || $code == 'CP936'){
            $res = mb_convert_encoding($res, 'utf-8', 'gbk');
        }
        
        return $res;
    }

    /**
     * get请求
     */
    public function httpGet($url){

        // $url = "http://www.cdbottle.com/";

        $start_time = time();

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        /**不验证ssl */
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        /**文本流返回 */
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        /**跟随跳转 */
        curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1);

        /**超时 */
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        //useragent
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"); 

        /** REFERER(伪造来路)*/
        // curl_setopt ($curl, CURLOPT_REFERER, "https://www.nvshens.org/");  

        // /**伪装百度爬虫 */
        // $ip=mt_rand(11, 191).".".mt_rand(0, 240).".".mt_rand(1, 240).".".mt_rand(1, 240); 
        // $header = array(
        //     'CLIENT-IP:'.$ip,
        //     'X-FORWARDED-FOR:'.$ip,
        // );//构造ip
        // curl_setopt($curl, CURLOPT_USERAGENT, 'Baiduspider+(+http://www.baidu.com/search/spider.htm)');
        // curl_setopt ($curl, CURLOPT_HTTPHEADER, $header);

        //代理:9401
        // curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式

        // $ipInfo = $this->getIp();
        // if($ipInfo['ip']){
        //     curl_setopt($curl, CURLOPT_PROXY, $ipInfo['ip']); //代理服务器地址  
        //     curl_setopt($curl, CURLOPT_PROXYPORT,$ipInfo['port']); //代理服务器端口
        //     curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        // }


        $rawData = @curl_exec($curl);

        $end_time = time();

        echo date("Y-m-d H:i:s")."：抓取时间:" . ($end_time - $start_time)."s\r\n";

        Log::notice(date("Y-m-d H:i:s").'：抓取页面,网址:'.$url.',耗时'. ($end_time - $start_time) . '秒');

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
            curl_close($curl);
            return false;
        }

        curl_close($curl);
        if(mb_strpos($rawData,'404 - ') !== false){
            echo "未成功抓取图片";
            return false;
        }

        return $rawData;
    }

    // public function getIp(){
    //     $pool = [
    //         // [
    //         //     'ip' => '58.220.95.54',
    //         //     'port' => 9400
    //         // ],
    //         // [
    //         //     'ip' => '221.5.80.66',
    //         //     'port' => 3128
    //         // ],
    //         // [
    //         //     'ip' => '183.220.145.3',
    //         //     'port' => 80
    //         // ],
    //         [
    //             'ip' => '',
    //             'port' => '',
    //         ]
    //     ];

    //     $index = array_rand($pool,1);

    //     return $pool[$index];
    // }

}