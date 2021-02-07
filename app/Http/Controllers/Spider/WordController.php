<?php

namespace App\Http\Controllers\Spider;
use Illuminate\Support\Facades\Log;

/**
 * 第一版主图片文字类
 */
class WordController {

    /**网站地址 */
    protected $web_url;

    /**每次取出数据量 */
    protected $handle_size = 100;

    // /**当前书籍id */
    // protected $current_book_id = null;

    // /**当前章节id */
    // protected $current_chapter_id = null;

    /**章节model */
    protected $chapter_model;

    /**文字model */
    protected $word_model;

    /**从哪本书开始扫描(bookid) */
    protected $book_start;

    /**从哪本书结束扫描(bookid) */
    protected $book_end;

    public function __construct($book_start = null ,$book_end = null)
    {   
        $this->chapter_model = New \App\Models\ChapterModel();
        $this->word_model = New \App\Models\WordModel();
        $web_model = New \App\Models\WebsiteModel();
        $this->book_start = $book_start;
        $this->book_end = $book_end;
        $this->web_url = $web_model->find(1)->url;
        
    }

    public function handle(){
        $condition[] = ['img_is_scan' , '=' , 0];

        $condition[] = ['is_spider' , '=' , 1];

        if($this->book_start){
            $condition[] = ['book_id','>=',$this->book_start];
        }

        if($this->book_end){
            $condition[] = ['book_id','<=',$this->book_end];
        }

        $total_num = $this->chapter_model->where($condition)->count();

        echo "开始解析图片数据,共计". $total_num . "章节数据\r\n\r\n";

        $is_end = false;

        while(!$is_end){
            $chapter_list = $this->chapter_model->where($condition)->get();

            if($chapter_list->isEmpty()){
                break;
            }

            foreach ($chapter_list as $key => $value) {

                $num = $key + 1;

                $this->current_book_id = $value->book_id;
                $this->current_chapter_id = $value->id;

                echo "开始解析第". $num . "章图片\r\n";
                $this->handleChapter($value,$num);


            }
        }

        echo "当前所有图片解析完毕\r\n\r\n";exit;
    }

    /**
     * 解析单个章节图片
     * @ch
     */
    public function handleChapter($chapter_data,$num){
        $content_list = $this->getWordComntent($chapter_data->source_content);

        if(!$content_list){
            $chapter_data->img_is_scan = 3;
            $chapter_data->save();

            echo "第". $num . "章数据解析失败,置为异常状态\r\n";
            return false;
        }

        echo "第". $num . "章数据解析成功,共有" . count($content_list[0]) . "页\r\n";

        foreach ($content_list[0] as $key => $value) {

            echo "开始扫描第". $num . "章第" . ($key + 1) . "页图片\r\n";

            /**正则匹配整理图片资源 */
            $img_array = $this->regexImg($value);

            /**如果该章节图片没有爬取完状态设置为4 */
            if($img_array['is_all'] == false){
                $chapter_data->img_is_scan = 4;
            }else{
                $chapter_data->img_is_scan = 1;
            }

            $chapter_data->save();


            echo "第". $num . "章第" . ($key + 1) . "页图片扫描完毕\r\n\r\n";
        }

    }

    public function regexImg($str){

        preg_match_all("/<img src=\"(.*?)\".*?>/ism",$str,$match);
        $img_text_array = array_unique($match[0]);
        $img_url_array = array_unique($match[1]);

        $is_all_img = true;

        /**循环处理图片 */
        foreach ($img_text_array as $key => $value) {

            $temp = [];

            $local_url = null;
            

            $img_temp_array = explode('/',$img_url_array[$key]);
            $file_name = array_pop($img_temp_array);

            $temp['img_text'] = $value;
            $temp['origin_url'] = $img_url_array[$key];

            /**查询该字是否已经入库 */
            $word = $this->word_model->where('img_text',$temp['img_text'])->first();
            if($word){
                echo "该图片已入库\r\n";
                if(!$word->word){
                    /**对比md5查看是否已入库 */
                    $file_word = $this->word_model->where('md5',$temp['md5'])->whereNotNull('word')->first();

                    if($file_word){
                        echo "查询到图片对应文字:" . $file_word->word . "\r\n";
                        $word->word = $file_word->word;
                        $word->save();
                    }
                }
                continue;
            }

            /**爬取并保存图片 */
            echo "开始爬取图片" . $file_name . "\r\n";
            $img_data = $this->httpRequest($this->web_url . $img_url_array[$key]);

            if($img_data && $this->checkRes($img_data)){
                $local_url = $this->saveImg($file_name,$img_data);
                echo "图片" . $file_name . "以保存\r\n";
            }else{
                $is_all_img = false;
            }

            $temp['local_url'] = $local_url;
            $temp['md5'] = $this->getFileMd5($temp['local_url']);

            /**对比md5查看是否已入库 */
            $file_word = $this->word_model->where('md5',$temp['md5'])->whereNotNull('word')->first();

            if($file_word){
                echo "查询到图片对应文字:" . $file_word->word . "\r\n";
                $temp['word'] = $file_word->word;
            }else{
                $temp['word'] = null;
            }

            $temp['created_at'] = date("Y-m-d H:i:s",time());

            $this->word_model->insert($temp);
        }

        return ['is_all' => $is_all_img];
    }

    /**
     * 保存图片
     */
    public function saveImg($fileName,$data = null){
        $saveDir = 'public' . DIRECTORY_SEPARATOR . 'word'. DIRECTORY_SEPARATOR . date("Y-m-d");

        if(!is_dir($saveDir)){
            mkdir ($saveDir,0777,true);
        }

        $savePath = $saveDir . DIRECTORY_SEPARATOR . $fileName;
 
        $path = base_path() . DIRECTORY_SEPARATOR . $savePath;
        $file = fopen($path,'w');
        fwrite($file,$data);
        fclose($file);
        return $savePath;
    }

    /**
     * 把章节分页
     */
    public function getWordComntent($str){
        $regexContent="/<div class=\"page-content font-large\".*?>.*?<\/div>/ism"; 
        if(preg_match_all($regexContent, $str, $matches)){

            return $matches;

        }else{
            return false;
        }
    }

    /*检查是否返回了图片*/
    public function checkRes($data){
        if(!$data){
            return false;
        }
        if(strpos($data,'<title>404</title>') === false){
            return true;
        }else{
            return false;
        }
        
    }

    /**
     * 获取图片md5码
     * @param local_url string 图片本地储存地址
     */
    function getFileMd5($local_url){
        $md5 = null;

        $path = base_path() . DIRECTORY_SEPARATOR . $local_url;

        if(file_exists($path)){
            $md5 = md5_file($path);
        }
        
        return $md5;
    }

    /**
     * @param url string 请求地址
     * @param request_times int 重复请求次数
     * @param request_time array 每次请求时间间隔(第一个参数固定为0)
     */
    public function httpRequest($url,$request_times = 3,$request_time = [0,30,60]){

        ini_set('memory_limit', '256M');

        $use_times = 0;

        while($use_times < $request_times){
            /**
             * 判断睡眠时间
             */
            $sleep_time = $request_time[$use_times];

            if($sleep_time){
                echo "图片请求失败,沉睡" . $sleep_time . "秒后再次请求\r\n";
                sleep($sleep_time);
            }

            $res = $this->httpGet($url);

            /**
             * 如果请求成功跳出循环
             */
            if($res){
                break;
            }

            $use_times++;
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

        return $rawData;
    }
}