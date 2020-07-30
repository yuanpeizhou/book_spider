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

    public function __construct()
    {
        $this->model = New \App\Models\WordModel();
        $this->common = New \App\Lib\Common();

        $this->book_model = New \App\Models\BookModel();
        $this->chapter_model = New \App\Models\ChapterModel();
        $this->word_model = New \App\Models\WordModel();
        $web_model = New \App\Models\WebsiteModel();
        $this->web_url = $web_model->find(1)->url;
    }
    // api/count/user,api/count/activity,api/count/reservation


    /*根据标签处理*/
    public function handle(){
        $condition[] = ['img_is_scan','=',0];

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

            request()->offsetSet('page',$i);
            $chapter_list = $this->chapter_model->select('id','source_content')->where($condition)->orderBy('id')->simplePaginate($this->chapter_size);

            if(!$chapter_list){
                continue;
            }
            $chapter_id_list = [];
            foreach ($chapter_list as $key => $value) {
                echo "id：".$value->id."\r\n";
                $this->handleImg($value->source_content);
                
                $chapter_id_list[] = $value->id;

                echo "第" . $i . "页第" . $key . "章数据处理完毕,$i/$this->chapter_page\r\n";
            }
            $chapter_model = New \App\Models\ChapterModel();
            $res = $chapter_model->whereIn('id',$chapter_id_list)->update(['img_is_scan' => 1]);

            if(!$res){
                echo "插入失败\r\n\r\n";die;
            }
            // var_dump($chapter_id_list);
            
            echo "处理完成,休眠1秒钟\r\n\r\n";
            sleep(2);
        }
        echo "所有书籍扫描完毕\r\n";
    }

    public function handleImg($data){
        $content_list = $this->getWordComntent($data,'complex');
        if(!$content_list){
            var_dump($content_list);exit;
            // dd($data);
        }
        echo "该章节共计". count($content_list[0]) . "页\r\n";

        foreach ($content_list[0] as $key => $value) {
            /**正则匹配整理图片资源 */
            $img_array = $this->regexImg($value);

            /**图片资源入库 */
            $this->word_model->insert($img_array);

            echo "第". $key . "页数据扫描完毕\r\n";
        }
        echo "章节数据扫描完毕\r\n\r\n";
    }

    public function regexImg($str){

        $word_list = $this->word_model->pluck('img_text')->toArray();

        preg_match_all("/<img src=\"(.*?)\".*?>/ism",$str,$match);
        $img_text_array = array_unique($match[0]);
        $img_url_array = array_unique($match[1]);

        $res = [];

        foreach ($img_text_array as $key => $value) {

            $temp = [];

            if(!in_array($value,$word_list)){
                $temp['img_text'] = $value;
                $temp['origin_url'] = $img_url_array[$key];

                /**抓取图片到本地 */
                $img_data = $this->getPageData($this->web_url . $img_url_array[$key],false);
                if($this->checkRes($img_data)){
                    $img_temp_array = explode('/',$img_url_array[$key]);
                    $fileName = array_pop($img_temp_array);
                    $temp['local_url'] = $this->saveImg($fileName,$img_data);
                }else{
                    $temp['local_url'] = '未采集到该资源';
                }

                $temp['created_at'] = date("Y-m-d H:i:s",time());

                $res[] = $temp;
            }
        }
        
        return $res;
    }


    /*检查是否返回了图片*/
    public function checkRes($data){
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






