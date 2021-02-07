<?php

namespace App\Http\Controllers\Spider;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
/**
 * 颜控
 */
class YankongController{
    protected $webUrl = 'http://www.ykmeinv.com/';
    protected $homeUrl = 'http://www.ykmeinv.com/yan/kongnvm/25_1.html';

    public function __construct()
    {
        $this->imgModel = New \App\Models\YankongImgModel();

        $this->imgsModel = New \App\Models\YankongImgsModel();

        $this->limit = 100;
    }

    public function test(){
        // $data = $this->httpRequest($this->web_url);
        // echo $data;
        $this->spider();
    }

    /**
     * 扫描套图资源
     * @param model string 爬取模式 init全量爬取 update增量爬取(爬取到数据库已有数据会停止)
     */
    public function scan(){
        $url = $this->homeUrl;
        $homeData = $this->httpRequest($url);

        // echo $homeData;exit;

        if(!$homeData){
            echo "首页数据爬取失败\r\n\r\n";die;
        }

        $homeImgCount = $this->saveImgList($homeData);

        echo "首页套图录入成功,共录入". $homeImgCount . "条数据\r\n\r\n";

        $lastPage = $this->getLastPage($homeData);

        if(!$lastPage){
            echo "分析页数失败\r\n\r\n";die;
        }else{
            echo "分析页数成功,共".$lastPage."页\r\n\r\n";
        }

        for ($i = 2; $i <= $lastPage; $i++) { 
            $url = 'http://www.ykmeinv.com/yan/kongnvm/25_' . $i . '.html';
            $pageData = $this->httpRequest($url);
            $pageImgCount = $this->saveImgList($pageData);
            echo '第'. $i . "页数据录入成功,共计" . $pageImgCount . "条数据\r\n";
        }

        echo "套图信息爬取完毕\r\n\r\n";die;
    } 

    /**
     * 爬取套图资源
     */
    public function spider(){
        $is_last = false;
        while(!$is_last){
            $imgList = $this->imgModel->where('is_spider',0)->paginate($this->limit);

            if($imgList->isEmpty()){
                $is_last = true;
            }

            foreach ($imgList as $key => $value) {
                $url = $value->url;
                $imgsList = [];
                $this->imgIsTrue = true;

                echo "开始爬取《".$value->name."》\r\n";

                $homeData = $this->httpRequest($url);

                $imgsLastPage = $this->getImgsLastPage($homeData);

                if($imgsLastPage == 1){
                    echo "该套图解析页数失败,置为特殊状态!!\r\n";
                    $value->is_spider = 2;
                    $value->save();
                    continue;
                }
                
                $imgsList = array_merge($this->getImgsContent($homeData),$imgsList);

                echo "第1页图片列表解析完成\r\n";

                for ($i= 2; $i <= $imgsLastPage; $i++) { 
                    $tempUrl = str_replace('.html','',$url) . '_' . $i . '.html';
                
                    $pageData = $this->httpRequest($tempUrl);

                    $imgsList = array_merge($this->getImgsContent($pageData),$imgsList);

                    echo "第". $i ."页图片列表解析完成\r\n";
                }
                echo "\r\n";
                echo "所有图片解析完毕，共计".count($imgsList)."张,开始爬取图片资源\r\n";

                foreach ($imgsList as $imgs_key => $imgs_value) {
                    $imgsUrl = $imgs_value;
                    $order = $imgs_key + 1;
                    $name = \trim($value->name);

                    $imgs = $this->imgsModel->where('img_id',$value->id)->where('order',$order)->first();

                    if($imgs && $imgs->is_spider == 1){
                        echo "图片". $order . "已录入,跳过\r\n";
                        continue;
                    }

                    $imgFile = $this->httpRequest($imgsUrl);

                    if(!$imgs){
                        $imgTemp['img_id'] = $value->id;
                        $imgTemp['origin_url'] = $imgsUrl;
                        $imgTemp['created_at'] = date("Y-m-d H:i:s");
                        $imgTemp['order'] = $imgs_key + 1;
    
                        if($imgFile){
                            $imgTemp['local_url'] = $this->saveImg($name,$imgFile,$order);
                            $imgTemp['is_spider'] = 1;
                            echo "图片".$order."爬取成功\r\n";
                        }else{
                            $this->imgIsTrue = false;
                            echo "图片".$order."爬取失败\r\n";
                        }
    
                        $this->imgsModel->insert($imgTemp);
                    }else{
                        if($imgFile){
                            $imgs->local_url = $this->saveImg($name,$imgFile,$order);
                            $imgs->is_spider = 1;
                            $imgs->save();
                            echo "图片".$order."爬取成功\r\n";
                        }else{
                            $this->imgIsTrue = false;
                            echo "图片".$order."爬取失败\r\n";
                        }
                    }
                }

                if($this->imgIsTrue == true){
                    $value->is_spider = 1;
                    $value->number = count($imgsList);
                    $value->updated_at = date("Y-m-d H:i:s");
                    $value->save();
                }else{
                    $value->is_spider = 2;
                    $value->number = count($imgsList);
                    $value->updated_at = date("Y-m-d H:i:s");
                    $value->save();
                }
                
                echo "《". $value->name ."》" . "图片资源爬取完毕\r\n\r\n";
            }
        }
        echo "所有套图资源爬取完毕,爬虫停止\r\n\r\n";die; 
    }

    /**
     * 保存套图信息
     */
    public function saveImgList($str){
        $count = 0;
        $imgList = $this->getImgListByContent($str);

        $insertData = [];
        foreach ($imgList['name'] as $key => $value) {
            $url = $this->webUrl . $imgList['url'][$key];

            $is_repeat = $this->imgModel->where('url',$url)->first();

            if(!$is_repeat){
                $temp['name'] = $value;
                $temp['url'] = $url;
                $temp['created_at'] = date("Y-m-d H:i:s");
                $insertData[] = $temp;
                $count++;
            }
        }
        $res = $this->imgModel->insert($insertData);

        return $count;
    }

    /**
     * 解析出套图列表
     */
    public function getImgListByContent($str){
        $regex ="/<ul>(.*?)<\/ul>/ism";
        if(preg_match_all($regex, $str, $matches)){
            $regex1 ="/<div class=\"title\"><a href=\'(.*?)\'>(.*?)<\/a><\/div>/ism";
            if(preg_match_all($regex1, $matches[1][0], $matches1)){
                return ['name' => $matches1[2] , 'url' => $matches1[1]];
            }else{
                return [];
            }
        }else{
            return [];
        }
    }

    /**
     * 获取最后一页
     */
    public function getLastPage($str){
        // echo $str;exit;
        $regex = "/<div class=\"pagelist\">(.*?)<\/div>/ism";
        if(preg_match_all($regex, $str , $matches)){
            $regex1 ="/<a.*?>(.*?)<\/a>/ism";
            if(preg_match_all($regex1, $matches[0][0], $matches1)){
                $temp = array_pop($matches1[0]);
                $temp = explode('_',$temp);
                $temp = array_pop($temp);
                $temp = explode('.',$temp);
                $temp = array_shift($temp);
                return $temp;
            }else{
                echo '234';exit;
                return 1;
            }
        }else{
            var_dump(123);exit;
            return 1;
        }
    }

    /**
     * 获取图片资源页数
     */
    public function getImgsLastPage($str){
        $regex = "/<div class=\'pagelist\'>(.*?)<\/div>/ism";
        if(preg_match_all($regex, $str , $matches)){
            $regex1 ="/<a.*?>(.*?)<\/a>/ism";
            if(preg_match_all($regex1, $matches[1][0], $matches1)){
                array_pop($matches1[1]);
                return array_pop($matches1[1]);
            }else{
                return 1;
            }
        }else{
            return 1;
        }
        
    }

    /**
     * 获取图片资源内容块
     */
    public function getImgsContent($str){
        $regex = "/<div id=\'picg\'>(.*?)<\/div>/ism";
        if(preg_match_all($regex, $str , $matches)){
            $regex1 ="/<img src=\"(.*?)\".*?\/>/ism";
            if(preg_match_all($regex1, $matches[1][0], $matches1)){
                return $matches1[1];
            }else{
                return [];
            }
        }else{
            return [];
        }
    }

    /*保存图片*/
    public function saveImg($dirName,$data = null,$fileName){
        $saveDir =  base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'yan_img' . DIRECTORY_SEPARATOR . $dirName;
        if(!is_dir($saveDir)){
            try {
                mkdir ($saveDir,0777,true);
            } catch (\Throwable $th) {
                $saveDir = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'yan_img' . DIRECTORY_SEPARATOR . '失败';
            }
        }
        $fileName = $fileName . '.png';
        $path = $saveDir . DIRECTORY_SEPARATOR . $fileName;

        $file = fopen($path,'w');
        fwrite($file,$data);
        fclose($file);
        $savePath = 'public' . DIRECTORY_SEPARATOR . 'yan_img' . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR .$fileName;
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
            echo "页面请求失败，延时3秒尝试第二次请求\r\n";
            sleep(3);
            $res = $this->httpGet($url);
        }

        if(!$res){
            echo "页面请求失败，延时3秒尝试第三次请求\r\n";
            sleep(3);
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
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        //useragent
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"); 

        /** REFERER(伪造来路)*/
        curl_setopt ($curl, CURLOPT_REFERER, "https://www.yunu8.xyz/");  

        $rawData = @curl_exec($curl);

        $end_time = time();

        echo date("Y-m-d H:i:s")."：抓取时间:" . ($end_time - $start_time)."s\r\n";

        Log::notice(date("Y-m-d H:i:s").'：抓取页面,网址:'.$url.',耗时'. ($end_time - $start_time) . '秒');

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
            curl_close($curl);
            return false;
        }

        // curl_close($curl);
        // if(mb_strpos($rawData,'404 - ') !== false){
        //     echo "未成功抓取图片";
        //     return false;
        // }

        return $rawData;
    }

}

