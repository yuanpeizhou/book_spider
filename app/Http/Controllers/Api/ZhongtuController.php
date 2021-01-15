<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
/**
 * 中国图书网
 */
class ZhongtuController extends CommonController{

    public function __construct()
    {   
        $this->webModel = New \App\Models\WebsiteModel();
        $web =  $this->webModel->find(6);

        $this->webUrl = $web->url;
        $this->start = $web->web_index;

        $this->type_model = New \App\Models\ZhongtuBookTypeModel();
    }

    /**
     * 爬取中图网书籍分类
     */
    public function spiderType(){
        $url = $this->webUrl . 'books/kinder/';

        $pageData = $this->httpRequest($url);

        $first_type_list = $this->getFirstTitle($pageData);
        $second_type_lists = $this->getSecondTitle($pageData);

        
        foreach ($first_type_list['title'] as $first_key => $first_value) {
            $first_index = $this->getIndex($first_type_list['index'][$first_key]);
            $first_is_repeat = $this->type_model->where('index',$first_index)->first();

            $first_data = [
                'name' => $first_value,
                'pid' => 0,
                'level' => 1,
            ];
            
            if($first_is_repeat){
                $first_id = $first_is_repeat->id;
                $first_data['updated_at'] = date("Y-m-d H:i:s");

                $this->type_model->where('index',$first_index)->update($first_data);

            }else{
                $first_data['index'] = $first_index; 
                $first_data['created_at'] = date("Y-m-d H:i:s");

                $first_id = $this->type_model->insertGetId($first_data); 
            }

            $id_path = $first_id;
            $name_path = $first_value;

            $first_path_data = [
                'id_path' => $id_path,
                'name_path' => $name_path
            ];

            $this->type_model->where('index',$first_index)->update($first_path_data);

            

            $second_type_list = $this->getSecondTitle2($second_type_lists[$first_key]);

            foreach ($second_type_list['title'] as $second_key => $second_value) {
                $second_index = $this->getIndex($second_type_list['index'][$second_key]);
                $second_is_repeat = $this->type_model->where('index',$second_index)->first();

                $second_data = [
                    'name' => $second_value,
                    'pid' => 1,
                    'level' => 2,
                ];

                if($second_is_repeat){
                    $second_id = $second_is_repeat->id;
                    $second_data['updated_at'] = date("Y-m-d H:i:s");
    
                    $this->type_model->where('index',$second_index)->update($second_data);
    
                }else{
                    $second_data['index'] = $second_index; 
                    $second_data['created_at'] = date("Y-m-d H:i:s");
    
                    $second_id = $this->type_model->insertGetId($second_data); 
                }
                
                $second_id_path = $id_path  .'-' . $second_id;
                $second_name_path = $name_path . '-' .$second_value;
    
                $second_path_data = [
                    'id_path' => $second_id_path,
                    'name_path' => $second_name_path,
                ];

                $this->type_model->where('index',$second_index)->update($second_path_data);

                $third_type_list = $this->getThidTypeList($second_index);

            }

            if($second_key == 2){
                var_dump('ok');exit;
            }


             
        }

        $sceond_title_list = $this->getSecondTitle($pageData);

        var_dump($sceond_title_list);exit;
    }

    public function getIndex($str){
        $array = explode('/',$str);
        $array = array_filter($array);

        return array_pop($array);
    }

    public function getThidTypeList($index){
        $url = $this->webUrl . 'kinder/' . $index . '/'; 

        $pageData = $this->httpRequest($url);

        $test = $this->getThirdTitle($pageData);

        // var_dump($test);exit;
    }

    public function getThirdTitle($str){
        $regex = "/<ul id=\"sele_catelist\">.*?<\/ul>/ism";
        if(preg_match_all($regex, $str , $matches)){

            if($matches){
                var_dump($matches);exit;
            }

            return $matches[0];

        }else{  
            return null;
        }
    }

    /**
     * 保存数据到数据库(检查是否存在该数据，不存在才写入)
     * 
     */
    public function saveToDatabase($index_field,$index,$model,$data,$update_field = []){
        $is_repeat = $model->where($index_field,$index)->first();

        if(!$is_repeat){
            $model->insert($data);
        }
    }

    public function getFirstTitle($str){
        $regex = "/<h2><a href=\"(.*?)\" target=\"_blank\">(.*?)<\/a><\/h2>/ism";
        if(preg_match_all($regex, $str , $matches)){
            // // return $matches[1][0];
            // var_dump($matches);exit;
            return ['title' => $matches[2] , 'index' => $matches[1]];
        }else{  
            return null;
        }
    }

    public function getSecondTitle($str){
        $regex = "/<ul>.*?<\/ul>/ism";
        if(preg_match_all($regex, $str , $matches)){

            array_shift($matches[0]);

            return $matches[0];

        }else{  
            return null;
        }
    }

    public function getSecondTitle2($str){
        $regex = "/<li><a href=\"(.*?)\" target=\"_blank\">(.*?)<\/a><\/li>/ism";
        if(preg_match_all($regex, $str , $matches)){
            return ['title' => $matches[2] , 'index' => $matches[1]];
        }else{  
            return null;
        }
    }

    /**
     * 入口
     */
    public function scan(){

        $this->spiderType();
        
        // echo "开始爬取\r\n";
        // for ($i = $this->start; $i >= 1; $i--) { 
        //     /**
        //      * 检查套图信息是否爬取完毕
        //      */
        //     $setImg = $this->aotubaModel->where('index',$i)->first();
        //     if($setImg && $setImg->is_spider == 1){
        //         continue;
        //     }

        //     $temp = [];
        //     $url = $this->webUrl . $this->model_url . '/' . $i . '.html';
        //     $pageData = $this->httpRequest($url);
        //     // echo $pageData;exit;
        //     $isPage = $this->checkPage($pageData);

        //     if($isPage){
        //         $title = $this->getTitle($pageData);
        //         // var_dump($title);exit;

        //         if(!$setImg){
        //             $temp['name'] = $title ? str_replace(' ','',$title) : null;
        //             $temp['url'] = $url;
        //             $temp['index'] = $i;
        //             $temp['created_at'] = date("Y-m-d H:i:s");
        //             $setImgId = $this->aotubaModel->insertGetId($temp);
        //         }else{
        //             $setImgId = $setImg->id;
        //         }

        //         echo "套图《".$title."》信息保存成功,开始爬取图片\r\n";

        //         $this->imgScan($setImgId,$pageData,$url,$title);

        //         $this->aotubaModel->where('id',$setImgId)->update(['is_spider' => $this->set_img_is_true ? 1 : 0 , 'number' => $this->aotubaImgModel->where('aotuba_img_id',$setImgId)->count()]);

        //         echo "套图《".$title."》爬取成功\r\n\r\n";

        //     }else{
        //         echo "未查询到该资源,跳过\r\n\r\n";
        //     }

        //     $this->webModel->where('id',5)->update(['web_index' => $i - 1]);
        //     $this->set_img_is_true = true;
        // }
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

        $start_time = time();

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        /**不验证ssl */
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        /**文本流返回 */
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        /**跟随跳转 */
        // curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1);

        /**超时 */
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        //useragent
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"); 

        /** REFERER(伪造来路)*/
        curl_setopt ($curl, CURLOPT_REFERER, $this->webUrl);  

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

        // if(mb_strpos($rawData,'404 - ') !== false){
        //     echo "未成功抓取图片";
        //     return false;
        // }

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