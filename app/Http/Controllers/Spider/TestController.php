<?php

namespace App\Http\Controllers\Spider;
// require_once 'vendor/autoload.php';
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Ocr\V20181119\OcrClient;
use TencentCloud\Ocr\V20181119\Models\GeneralBasicOCRRequest;
/**
 * 落花有声网
 */
class TestController {

    protected $web_url = 'http://v.luohuays.me/playbook/?931-0-0.html';

    public function test(){
        $data = $this->httpRequest($this->web_url);
        echo $data;
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
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        //useragent
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"); 

        /** REFERER(伪造来路)*/
        curl_setopt ($curl, CURLOPT_REFERER, "http://v.luohuays.me/");  

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

    // public function ocr(){

    // }

    public function ocr(){

        $url = 'http://www.diyibanzhu2.in//toimg/data/3016272389.png';

        $img_data = $this->getData($url,false,false);

        $this->saveImg(null,$img_data);

        // var_dump($img_data);exit;

        // $path = base_path() . DIRECTORY_SEPARATOR . 'public\word\8217289262.png';

        // $file = \file_get_contents($path);

        // $file_base64 = base64_encode($file);

        // try {

        //     $cred = new Credential("AKIDIO6h5TkdNSPTKusnuoVv4fz5wqVwm1mZ", "vxpvQ5lDVdYP6buFOQaZKtLdU91mAJPK");
        //     $httpProfile = new HttpProfile();
        //     $httpProfile->setEndpoint("ocr.tencentcloudapi.com");
              
        //     $clientProfile = new ClientProfile();
        //     $clientProfile->setHttpProfile($httpProfile);
        //     $client = new OcrClient($cred, "ap-guangzhou", $clientProfile);

        //     // $client->setDefaultOption('verify', false);
        
        //     $req = new GeneralBasicOCRRequest();

        //     $path = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'word' . DIRECTORY_SEPARATOR . 'public\word\0152730126.png';

        //     $params = array(
        //         "ImageUrl" => "http://www.diyibanzhu2.in//toimg/data/6831680769.png"
        //         // "ImageBase64" => $file_base64
        //     );
        //     $req->fromJsonString(json_encode($params));
        
        //     $resp = $client->GeneralBasicOCR($req);
        
        //     print_r($resp->toJsonString());
        // }
        // catch(TencentCloudSDKException $e) {
        //     var_dump($e);exit;
        // }
    }

    /*保存图片*/
    public function saveImg($fileName,$data = null){
        $savePath = 'public' . DIRECTORY_SEPARATOR . 'test.png';
        $path = base_path() . DIRECTORY_SEPARATOR . $savePath;
        $file = fopen($path,'w');
        fwrite($file,$data);
        fclose($file);
        // return $path;
        // echo '接收文件'.$fileName;
        return $savePath;
    }

    public static function getData($url, $decode = true, $assoc = true){

        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt ($curl, CURLOPT_REFERER, "http://www.diyibanzhu2.in/");  


        $rawData = @curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
            curl_close($curl);
            return false;
        }

        if(strpos($rawData,'NAME="robots"') !== false){
            curl_close($curl);
            return false;
        }

        curl_close($curl);
        
        if($decode){
            return json_decode($rawData, $assoc);
        }else{
            return $rawData;
        }
    }

}

