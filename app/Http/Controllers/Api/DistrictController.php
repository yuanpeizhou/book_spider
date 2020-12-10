<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
/**
 * 国家统计局行政区域划分
 */
class DistrictController extends CommonController{

    public function __construct()
    {   
        $this->webModel = New \App\Models\WebsiteModel();
        $web =  $this->webModel->find(4);
        $this->webUrl = $web->url;
        // $this->start = $web->web_index;
        $this->model_url = '2020';

        $this->model = New \App\Models\DistrictModel();
        $this->province_start_code = '44';
        $this->city_start_code = '445100000000';
    }

    /**
     * 入口
     */
    public function scan(){
        // $this->provinceSpider();
        // $this->citySpider();
        // $this->districtSpider();
        $this->streetSpider();
    }

    // if(str_replace('.html','',$province_value['url']) < $this->province_start_code){
    //     continue;
    // }

    // $province_id = $this->provinceSpider($province_value);
    // sleep(1);

    // /**
    //  * 获取市级地区列表
    //  */
    // $city_url = $this->webUrl . $this->model_url . '/' . $province_value['url'];
    // $city_res = $this->httpRequest($city_url);
    // $city_list = $this->cityRegex($city_res);

    // foreach ($city_list as $city_key => $city_value) {
    //     if($city_value['code'] < $this->city_start_code){
    //         continue;
    //     }

    //     sleep(1);
    //     $city_id = $this->citySpider($city_value);

    //     /**
    //      * 获取区级地区列表
    //      */
    //     $district_url = $this->webUrl . $this->model_url . '/'  . $city_value['url'];
    //     $district_res = $this->httpRequest($district_url);
    //     $district_list = $this->districtRegex($district_res);
    //     if($district_list){
    //         foreach ($district_list as $district_key => $district_value) {

    //             sleep(1);

    //             /**
    //              * 获取街道列表
    //              */
    //             if(!$district_value['url']){
    //                 continue;
    //             }
    //             $street_url = $this->webUrl . $this->model_url . '/'. str_replace('.html','',$province_value['url']) . '/'  . $district_value['url'];
    //             $street_res = $this->httpRequest($street_url);
    //             $street_list = $this->streetRegex($street_res);
    //             foreach ($street_list as $street_key => $street_value) {
    //                 echo $street_value['name']."\r\n";
    //                 $street_temp['parent_id'] = $district_id;
    //                 $street_temp['year'] =  $this->model_url;
    //                 $street_temp['code'] = $street_value['code'];
    //                 $street_temp['name'] = $street_value['name'];
    //                 $street_temp['level'] = 4;
    //                 $street_id = $this->model->insertGetId($street_temp);
    //                 sleep(1);

    //                 /**
    //                  * 获取居委会列表
    //                  */
    //                 $url_array = explode('/',$street_url);
    //                 array_pop($url_array);
    //                 $address_url = implode('/',$url_array) . '/' .$street_value['url'];
    //                 $address_res = $this->httpRequest($address_url);
    //                 $address_list = $this->addressRegex($address_res);

    //                 foreach ($address_list as $address_key => $address_value) {
    //                     echo $address_value['name']."\r\n";
    //                     $address_temp['parent_id'] = $street_id;
    //                     $address_temp['year'] =  $this->model_url;
    //                     $address_temp['code'] = $address_value['code'];
    //                     $address_temp['name'] = $address_value['name'];
    //                     $address_temp['type'] = $address_value['type'];
    //                     $address_temp['level'] = 5;
    //                     $this->model->insert($address_temp);
    //                 }
    //             }
    //         }
    //     }else{
    //         /**
    //          * 获取街道列表
    //          */
    //         $street_url =  $this->webUrl . $this->model_url . '/'  . $city_value['url'];
    //         $street_res = $this->httpRequest($street_url);
    //         $street_list = $this->streetRegex($street_res);
    //         foreach ($street_list as $street_key => $street_value) {
    //             echo $street_value['name']."\r\n";
    //             $street_temp['parent_id'] = $city_id;
    //             $street_temp['year'] =  $this->model_url;
    //             $street_temp['code'] = $street_value['code'];
    //             $street_temp['name'] = $street_value['name'];
    //             $street_temp['level'] = 4;
    //             $street_id = $this->model->insertGetId($street_temp);
    //             sleep(1);

    //             /**
    //              * 获取居委会列表
    //              */
    //             $url_array = explode('/',$street_url);
    //             array_pop($url_array);
    //             $address_url = implode('/',$url_array) . '/' .$street_value['url'];
    //             $address_res = $this->httpRequest($address_url);
    //             $address_list = $this->addressRegex($address_res);

    //             foreach ($address_list as $address_key => $address_value) {
    //                 echo $address_value['name']."\r\n";
    //                 $address_temp['parent_id'] = $street_id;
    //                 $address_temp['year'] =  $this->model_url;
    //                 $address_temp['code'] = $address_value['code'];
    //                 $address_temp['name'] = $address_value['name'];
    //                 $address_temp['type'] = $address_value['type'];
    //                 $address_temp['level'] = 5;
    //                 $this->model->insert($address_temp);
    //             }
    //         }
    //     }

    // }

    /**
     * 爬取省级列表
     */
    public function provinceSpider(){

        $url = $this->webUrl . $this->model_url . '/';
        $res = $this->httpRequest($url);
        $province_list = $this->provinceRegex($res);

        foreach ($province_list as $province_key => $province_value) {

            echo $province_value['name']."\r\n";

            $code = str_replace('.html','',$province_value['url']);

            $province = $this->model->where('code',$code)->first();

            if(!$province){
                $temp['parent_id'] = 0;
                $temp['year'] = $this->model_url;
                $temp['code'] = $code;
                $temp['name'] = $province_value['name'];
                $temp['level'] = 1;
                $temp['url'] = $province_value['url'];
                $temp['create_time'] = date("Y-m-d H:i:s");
                $this->model->insert($temp);
            }
        }
    }

    /**
     * 爬取市级列表
     */
    public function citySpider(){
        $province_list = $this->model->where('level',1)->get();

        foreach ($province_list as $key => $value) {
            $city_url = $this->webUrl . $this->model_url . '/' . $value['url'];
            $city_res = $this->httpRequest($city_url);
            $city_list = $this->cityRegex($city_res);

            foreach ($city_list as $city_key => $city_value) {
                echo $city_value['name']."\r\n";
                $city = $this->model->where('code',$city_value['code'])->first();
                if(!$city){
                    $city_temp['parent_id'] = $value['id'];
                    $city_temp['year'] =  $this->model_url;
                    $city_temp['code'] = $city_value['code'];
                    $city_temp['name'] = $city_value['name'];
                    $city_temp['level'] = 2;
                    $city_temp['url'] = $city_value['url'];
                    $city_temp['create_time'] = date("Y-m-d H:i:s");
                    $this->model->insert($city_temp);
                }
            }
        }
    }
    
    /**
     * 爬取区县级列表
     */
    public function districtSpider(){
        $city_list = $this->model->where('level',2)->where('is_spider',0)->get();

        foreach ($city_list as $key => $value) {
            $url = $this->webUrl . $this->model_url . '/'  . $value['url'];

            $district_res = $this->httpRequest($url);
            $district_list = $this->districtRegex($district_res);

            if($district_list){
                foreach ($district_list as $district_key => $district_value) {
                    echo $district_value['name']."\r\n";
                    $district = $this->model->where('code',$district_value['code'])->first();

                    if(!$district){
                        $district_temp['parent_id'] = $value['id'];
                        $district_temp['year'] =  $this->model_url;
                        $district_temp['code'] = $district_value['code'];
                        $district_temp['name'] = $district_value['name'];
                        $district_temp['level'] = 3;
                        $district_temp['url'] = substr($value['url'],0,2) . '/'  . $district_value['url'];
                        $district_temp['create_time'] = date("Y-m-d H:i:s");
                        $this->model->insert($district_temp);
                    }
                }
            }else{
                $street_res = $this->httpRequest($url);
                $street_list = $this->streetRegex($street_res);
                foreach ($street_list as $street_key => $street_value) {
                    echo $street_value['name']."\r\n";

                    $street = $this->model->where('code',$street_value['code'])->first();

                    if(!$street){
                        $street_temp['parent_id'] = $value['id'];
                        $street_temp['year'] =  $this->model_url;
                        $street_temp['code'] = $street_value['code'];
                        $street_temp['name'] = $street_value['name'];
                        $street_temp['level'] = 4;
                        $street_temp['url'] = substr($value['url'],0,2) . '/'  . $street_value['url'];
                        $street_temp['create_time'] = date("Y-m-d H:i:s");
                        $this->model->insert($street_temp);
                    }
                }
            }
            echo $value['name']."爬取完毕\r\n\r\n";
            $this->model->where('id',$value['id'])->update(['is_spider' => 1]);
        }
        echo "complete,it's ok";
    }

    /**
     * 爬取街道列表
     */
    public function streetSpider(){
        $district_list = $this->model->where('level',3)->where('is_spider',0)->get();
        foreach ($district_list as $key => $value) {
            $url = $this->webUrl . $this->model_url . '/'  . $value['url'];

            $street_res = $this->httpRequest($url);
            $street_list = $this->streetRegex($street_res);

            foreach ($street_list as $street_key => $street_value) {
                echo $street_value['name']."\r\n";

                $street = $this->model->where('code',$street_value['code'])->first();

                if(!$street){
                    $street_temp['parent_id'] = $value['id'];
                    $street_temp['year'] =  $this->model_url;
                    $street_temp['code'] = $street_value['code'];
                    $street_temp['name'] = $street_value['name'];
                    $street_temp['level'] = 4;
                    $street_temp['url'] = substr($value['url'],0,5) . '/'  . $street_value['url'];
                    $street_temp['create_time'] = date("Y-m-d H:i:s");
                    $this->model->insert($street_temp);
                }
            }

            echo $value['name']."爬取完毕\r\n\r\n";
            $this->model->where('id',$value['id'])->update(['is_spider' => 1]);
        }

        echo "complete,it's ok";
    }

    /**
     * 省级匹配
     */
    public function provinceRegex($str){
        $province_list = [];
        $regex ="/<tr class=\'provincetr\'>(.*?)<\/tr>/ism";
        if(preg_match_all($regex, $str, $matches)){
            $provinceRegex = "/<a href='(.*?)'>(.*?)<br\/><\/a>/i";
            foreach ($matches[1] as $key => $value) {
                if(preg_match_all($provinceRegex, $value, $provinceMatches)){
                    foreach ($provinceMatches[0] as $province_key => $province_value) {
                        $temp['url'] = $provinceMatches[1][$province_key];
                        $temp['name'] = $provinceMatches[2][$province_key];
                        $province_list[] = $temp;
                    }
                }else{
                    var_dump('false');
                }
            }
            
            return $province_list;

        }else{
            var_dump('false');
        }
    }

    public function cityRegex($str){
        $city_list = [];
        $regex ="/<tr class=\'citytr\'>(.*?)<\/tr>/ism";
        if(preg_match_all($regex, $str, $matches)){
            $cityRegex = "/<a href='(.*?)'>(.*?)<\/a>/i";
            // var_dump($matches);exit;
            foreach ($matches[1] as $key => $value) {
                if(preg_match_all($cityRegex, $value, $cityMatches)){
                    $temp['url'] = $cityMatches[1][0];
                    $temp['code'] = $cityMatches[2][0];
                    $temp['name'] = $cityMatches[2][1];
                    $city_list[] = $temp;
                }else{
                    var_dump('false');
                }
            }
            
            return $city_list;

        }else{
            var_dump('false');
        }
    }

    public function districtRegex($str){
        $district_list = [];
        $regex ="/<tr class=\'countytr\'>(.*?)<\/tr>/ism";
        if(preg_match_all($regex, $str, $matches)){
            $districtRegex = "/<a href='(.*?)'>(.*?)<\/a>/i";
            foreach ($matches[1] as $key => $value) {
                if(strpos($value,'</a>') !== false){
                    if(preg_match_all($districtRegex, $value, $districtMatches)){
                        $temp['url'] = $districtMatches[1][0];
                        $temp['code'] = $districtMatches[2][0];
                        $temp['name'] = $districtMatches[2][1];
                        $district_list[] = $temp;
                    }else{
                        return false;
                    }
                }else{
                    // $districtRegex2 = "/<td>(.*?)<\/td>/i";
                    // if(preg_match_all($districtRegex2, $value, $districtMatches2)){
                    //     $temp['url'] = null;
                    //     $temp['code'] = $districtMatches2[1][0];
                    //     $temp['name'] = $districtMatches2[1][1];
                    //     $district_list[] = $temp;
                    // }else{
                    //     return false; 
                    // }
                }

            }
            
            return $district_list;

        }else{
            return false;
        }
    }

    public function streetRegex($str){
        $street_list = [];
        $regex ="/<tr class=\'towntr\'>(.*?)<\/tr>/ism";
        if(preg_match_all($regex, $str, $matches)){
            $streetRegex = "/<a href='(.*?)'>(.*?)<\/a>/i";
            foreach ($matches[1] as $key => $value) {
                if(preg_match_all($streetRegex, $value, $streetMatches)){
                    $temp['url'] = $streetMatches[1][0];
                    $temp['code'] = $streetMatches[2][0];
                    $temp['name'] = $streetMatches[2][1];
                    $street_list[] = $temp;
                }else{
                    var_dump('false');
                }
            }
            
            return $street_list;

        }else{
            var_dump('false');
        }
    }

    public function addressRegex($str){
        $address_list = [];
        $regex ="/<tr class=\'villagetr\'>(.*?)<\/tr>/ism";
        if(preg_match_all($regex, $str, $matches)){
            
            $addressRegex = "/<td>(.*?)<\/td>/i";
            foreach ($matches[1] as $key => $value) {
                if(preg_match_all($addressRegex, $value, $addressMatches)){
                    $temp['code'] = $addressMatches[1][0];
                    $temp['type'] = $addressMatches[1][1];
                    $temp['name'] = $addressMatches[1][2];
                    $address_list[] = $temp;
                }else{
                    var_dump('false');
                }
            }
            
            return $address_list;

        }else{
            var_dump('false');
        }
    }



    /**
     * 获取标签
     */
    public function getTag($str){
        $regex ="/<ul id=\"utag\">(.*?)<\/ul>/i";
        if(preg_match_all($regex, $str, $matches)){
            if(isset($matches[1][0])){
                $tagRegex = "/<a.*?>(.*?)<\/a>/i";
                if(preg_match_all($tagRegex,$matches[1][0],$tagMatches)){
                    if($tagMatches[1]){
                        return $tagMatches[1];
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
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
        $regex ="/<div id=\"pages\">.*?<\/div>/i";
        if(preg_match_all($regex, $str, $matches)){
            $lastPageRegex = "/<a.*?href=\'(.*?)\' >.*?<\/a>/i";
            if(preg_match_all($lastPageRegex, $str, $lastPgaeMatches)){
                if(isset($lastPgaeMatches[1])){
                    return array_pop($lastPgaeMatches[1]);
                }else{
                    return false;
                }
            }else{
                return false;
            }
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
            // var_dump($str);
            return false;
        }
        
    }

    public function getImgList($str){
        $regex = "/<ul id=\"hgallery\">(.*?)<\/ul>/ism";

        if(preg_match_all($regex, $str , $matches)){
            $imgRegex = "/<img src=\'(.*?)\'.*?\/>/ism";
            if(preg_match_all($imgRegex,$matches[1][0],$imgMatches)){
                return $imgMatches[1];
            }else{

            }
        }else{  

        }



        var_dump($matches);exit;

        

        // $imgList = [];
        // foreach ($matches[0] as $key => $value) {

        //     if(strpos($value,'jpg') === false){
        //         continue;
        //     }

        //     if(strpos($value,'/static') !== false){
        //         $imgRegex = "/<img.*?file=\"(.*?)\".*?\/>/";
        //     }else{
        //         $imgRegex = "/<img.*?src=\"(.*?)\".*?\/>/";
        //     }

        //     preg_match_all($imgRegex, $value , $imgMatches);

        //     $imgList[] = $imgMatches[1][0];

        // }

        // return $imgList;
    }

    /*保存图片*/
    public function saveImg($dirName,$data = null,$fileName){
        $saveDir =  base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'set_img' . DIRECTORY_SEPARATOR . $dirName;
        if(!is_dir($saveDir)){
            mkdir ($saveDir,0777,true);
        }
        $fileName = $fileName . '.png';
        $path = $saveDir . DIRECTORY_SEPARATOR . $fileName;

        $file = fopen($path,'w');
        fwrite($file,$data);
        fclose($file);
        $savePath = 'public' . DIRECTORY_SEPARATOR . 'set_img' . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR .$fileName;
        // return $path;
        // echo '接收文件'.$fileName;
        return $savePath;
    }
    
    /**
     * 爬取请求(最多重复请求三次)
     * 
     */
    public function httpRequest($url){
        $res = $this->httpGet($url);

        if(!$res){
            echo "页面请求失败，延时2秒尝试第二次请求\r\n";
            sleep(2);
            $res = $this->httpGet($url);
        }

        if(!$res){
            echo "页面请求失败，延时2秒尝试第三次请求\r\n";
            sleep(2);
            $res = $this->httpGet($url);
        }

        if(!$res){
            echo "页面请求失败,跳过该页面\r\n";
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

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);

        curl_setopt($curl, CURLOPT_URL, $url);

        // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

        //useragent
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"); 

        /** REFERER*/
        curl_setopt ($curl, CURLOPT_REFERER, "http://www.stats.gov.cn");  

        // /**伪装百度爬虫 */
        // $ip=mt_rand(11, 191).".".mt_rand(0, 240).".".mt_rand(1, 240).".".mt_rand(1, 240); 

        // $header = array(
        //     'CLIENT-IP:'.$ip,
        //     'X-FORWARDED-FOR:'.$ip,
        // );//构造ip
        // curl_setopt($curl, CURLOPT_USERAGENT, 'Baiduspider+(+http://www.baidu.com/search/spider.htm)');

        $rawData = @curl_exec($curl);

        // curl_setopt ($curl, CURLOPT_HTTPHEADER, $header);

        $end_time = time();

        echo date("Y-m-d H:i:s")."：抓取时间:" . ($end_time - $start_time)."s\r\n";

        Log::notice(date("Y-m-d H:i:s").'：抓取页面,网址:'.$url.',耗时'. ($end_time - $start_time) . '秒');

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
            curl_close($curl);
            return false;
        }

        curl_close($curl);

        return $rawData;
    }

}