<?php
namespace App\Http\Controllers\Api;
use App\Exceptions\ParamsErrorException;

/*图片爬取处理*/
class WordController extends CommonController
{   
    protected $book_start;

    protected $book_end;

    protected $chapter_size = 100;

    protected $chapter_page;

    protected $web_url;

    protected $img_save_path;


    public function __construct()
    {
        $this->model = New \App\Models\WordModel();
        $this->common = New \App\Lib\Common();

        $this->book_model = New \App\Models\BookModel();
        $this->chapter_model = New \App\Models\ChapterModel();
        $this->word_model = New \App\Models\WordModel();
        $web_model = New \App\Models\WebsiteModel();
        $this->web_url = $web_model->find(1)->url;

        ini_set('memory_limit', '1024M');
    }
    // api/count/user,api/count/activity,api/count/reservation

    /**
     * 临时测试方法
     */
    public function md5(){
        $word_list = $this->word_model->get();
        foreach ($word_list as $key => $value) {
            $md5 = $this->getFileMd5($value);
            $value->md5 = $md5;
            $value->save();
        }
        echo 'ok';
        // for ($i=1817; $i <= 1817; $i++) { 
        //     $first_word = $this->model->find($i);

        //     if($first_word->word){
        //         $first_md5 = $this->getFileMd5($first_word);

        //         $word_list = $this->model->whereRaw('word is null')->get();
    
        //         $temp = [];
        
        //         foreach ($word_list as $key => $value) {
        
        //             $file_md5 = $this->getFileMd5($value);
        
        //             if($file_md5 == $first_md5){
        //                 $temp[] = $value->id;
        //             }
        //         }
        
        //         $res = $this->model->whereIn('id',$temp)->update(['word' => $first_word->word]);
        //     }
        // }

        // if(!$res){
        //     echo "error";
        // }else{
        //     echo "yes";
        // }
        // var_dump($temp);exit;
    }

    /**
     * 获取文件md5码
     */
    function getFileMd5($local_url){
        $md5 = null;

        $path = base_path() . DIRECTORY_SEPARATOR . $local_url;

        if(file_exists($path)){
            $md5 = md5_file($path);
        }
        
        return $md5;
    }


    /*扫描爬取图片并识别图片*/
    public function handle(){
        $condition[] = ['img_is_scan','=',0];

        $condition[] = ['is_spider','=',1];

        if($this->book_start){
            $condition[] = ['book_id','>=',$this->book_start];
        }

        if($this->book_end){
            $condition[] = ['book_id','<=',$this->book_end];
        }

        /** 确定分页*/ 
        $chapter_count = $this->chapter_model->where($condition)->count();

        $this->chapter_page = ceil($chapter_count/$this->chapter_size);

        echo "开始解析图片数据,共计". $chapter_count . "章节数据,共". $this->chapter_page ."页\r\n\r\n";

        $this->handleChapter($condition);
    }

    /**循环解析图片 */
    public function handleChapter($condition){

        for ($i=1; $i <= $this->chapter_page; $i++) { 
            echo "开始处理第". $i . "页章节\r\n";

            


            $id_list = $this->chapter_model->where('img_is_scan',0)->orderBy('id')->limit($this->chapter_size)->pluck('id');

            

            $chapter_list = $this->chapter_model->select('id','source_content')->whereIn('id',$id_list)->where('is_spider',1)->orderBy('id')->simplePaginate($this->chapter_size);

            if(!$chapter_list){
                echo "未查询到章节数据";continue;
            }


            $chapter_id_list = [];
            foreach ($chapter_list as $key => $value) {
                echo "id：".$value->id."\r\n";
                $res = $this->handleImg($value->source_content);

                if($res){
                    $chapter_id_list[] = $value->id;
                }

                echo "第" . $i . "页第" . $key . "章数据处理完毕,$i/$this->chapter_page\r\n";
            }
            
            $chapter_model = New \App\Models\ChapterModel();

            if($chapter_id_list){
                $res = $chapter_model->whereIn('id',$chapter_id_list)->update(['img_is_scan' => 1]);
            }
        }
        echo "所有书籍扫描完毕\r\n";
    }

    public function handleImg($data){
        $content_list = $this->getWordComntent($data,'complex');

        echo "该章节共计". count($content_list[0]) . "页\r\n";

        $res = true;

        foreach ($content_list[0] as $key => $value) {
            /**正则匹配整理图片资源 */
            $img_array = $this->regexImg($value);

            if($img_array['is_all'] == false){
                $res = false;
            }

            /**图片资源入库 */
            $wordModel = New \App\Models\WordModel();
            $wordModel->insert($img_array['data']);

            echo "第". $key . "页数据扫描完毕\r\n";
        }
        echo "章节数据扫描完毕\r\n\r\n";
        return $res;
    }

    public function regexImg($str){

        $word_list = $this->word_model->pluck('img_text')->toArray();

        preg_match_all("/<img src=\"(.*?)\".*?>/ism",$str,$match);
        $img_text_array = array_unique($match[0]);
        $img_url_array = array_unique($match[1]);

        $res = [];

        $is_all_img = true;
        /**循环处理图片 */
        foreach ($img_text_array as $key => $value) {

            $temp = [];
            

            $img_temp_array = explode('/',$img_url_array[$key]);
            $fileName = array_pop($img_temp_array);

            if(!in_array($value,$word_list)){
                $temp['img_text'] = $value;
                $temp['origin_url'] = $img_url_array[$key];
                $temp['word'] = null;
                $temp['created_at'] = date("Y-m-d H:i:s",time());

                $is_spider = false;
                $savePath = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'word' . DIRECTORY_SEPARATOR . $fileName;

                /**如果本地没有文件则抓取 */
                if(!file_exists($savePath)){
                    echo "开始抓取". $img_url_array[$key] ."\r\n";
                    /**抓取图片到本地 */
                    $img_data = $this->getPageData($this->web_url . $img_url_array[$key],false);
                    echo "成功抓取". $img_url_array[$key] ."开始校验\r\n";

                    if($this->checkRes($img_data)){
                        $is_spider = true;
                    }else{
                        echo "校验失败,开始第二次爬取";
                        /**抓取图片到本地 */
                        $img_data = $this->getPageData($this->web_url . $img_url_array[$key],false);
                        if($this->checkRes($img_data)){
                            $is_spider = true;
                        }else{
                            echo "校验失败,跳过该资源爬取\r\n";
                            $is_all_img= false;
                        }
                    }

                    if($is_spider){
                        echo "开始保存图片:" . $fileName."\r\n";
                        $temp['local_url'] = $this->saveImg($fileName,$img_data);
                    }
                }else{
                    $is_spider = true;
                    $temp['local_url'] = 'public' . DIRECTORY_SEPARATOR . 'word' . DIRECTORY_SEPARATOR . $fileName;
                }

                /**
                 * 查找到图片资源，对比md5值
                 */
                if($is_spider){
                    $temp['md5'] = $this->getFileMd5($temp['local_url']);
                    $file_word = $this->word_model->where('md5',$temp['md5'])->whereNotNull('word')->first();

                    if($file_word){
                        $this->word_model->where('md5',$temp['md5'])->whereNull('word')->update(['word' => $file_word->word]);
                        echo "查询到图片对应文字:" . $file_word->word . "\r\n\r\n";
                        $temp['word'] = $file_word->word;
                    }
                }
                $res[] = $temp;
            }
        }
        return ['is_all' => $is_all_img , 'data' => $res];
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

    /*保存图片*/
    public function saveImg($fileName,$data = null){
        $savePath = 'public' . DIRECTORY_SEPARATOR . 'word' . DIRECTORY_SEPARATOR . $fileName;
        $path = base_path() . DIRECTORY_SEPARATOR . $savePath;
        $file = fopen($path,'w');
        fwrite($file,$data);
        fclose($file);
        // return $path;
        // echo '接收文件'.$fileName;
        return $savePath;
    }

    public function getLetter(){
        $res = [];
        for($i=97; $i<122; $i++)
        {
            $res[] = chr($i);
        }
        return $res;
    }

    /*获取页面内容*/
    public function getWordComntent($str,$type = 'single'){
        $regexContent="/<div class=\"page-content font-large\".*?>.*?<\/div>/ism"; 
        if(preg_match_all($regexContent, $str, $matches)){

            return $matches;

        }else{
            return '';
        }
    }
}






