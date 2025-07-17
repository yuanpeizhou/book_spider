<?php
namespace App\Http\Controllers\Api;
use App\Exceptions\ParamsErrorException;
use App\Models\ChapterModel;
use App\Models\WebsiteModel;
use App\Models\WordModel;

/*图片爬取处理*/
class WordController extends CommonController
{   
    protected $current_book_id = null;

    protected $current_chapter_id = null;

    protected $book_start = 19;

    protected $book_end = 19;

    protected $chapter_size = 100;

    protected $chapter_page;

    protected $web_url;

    protected $img_save_path;

    protected $wordModel;

    protected $chapterModel;

    protected $websiteModel;

    protected $webiste;


    public function __construct($book_start = null ,$book_end = null)
    {
        $this->websiteModel = New WebsiteModel();
        $this->wordModel = New WordModel();
        $this->chapterModel = New ChapterModel();

        $this->webiste = $this->websiteModel->find(2);

        ini_set('memory_limit', '1024M');
    }




    /*扫描爬取图片并识别图片*/
    public function handle(){
        $handle = true;

        // 获取第一条
        $chapterFirst = $this->chapterModel->where("website_id", '=',$this->webiste->id)
            ->where("is_spider", 1)
            ->where('is_scan_img', 1)->first();
        $start = $chapterFirst ? $chapterFirst->id : 0;
        while($handle) {
            echo "从" . $start . "开始\r\n";
            $chapters = $this->chapterModel->where("website_id", '=',$this->webiste->id)
                ->where("is_spider", 1)
                ->where('is_scan_img', 1)
                ->where("id", ">=", $start)
                ->limit(500)->get();

            if (count($chapters) == 0) {
                $handle = false;
            }


            foreach ($chapters as $key => $chapter) {
                echo "开始扫描章节《" . $chapter->chapter_name . "》\r\n";
                $wordList = $this->getImgList($chapter->content);
                foreach ($wordList as $key => $item) {
                    // 先查下库里是否收录了这个图片,收录了跳过
                    $word = $this->wordModel->where('img', $item['url'])->first();
                    if ($word) {
                        continue;
                    }

                    $url = $this->webiste->url . $item['url'];
                    $imgData = $this->getPageData($url, $this->webiste, false);
                    
                    // 保存到本地
                    $tempArray = explode('/',$item['url']);
                    $fileName = array_pop($tempArray);
                    $this->saveImg($fileName, $imgData);

                    // 读取文件md5值
                    $filePath = $this->getFilePath($fileName);
                    $fileMd5 = $this->getFileMd5($filePath);

                    // 查下有没有相同的md5值
                    $word = null;
                    $repeat = $this->wordModel->where("md5", $fileMd5)->first();
                    if ($repeat) {
                        $word = $repeat->word;
                    }
                    
                    $wordData = [
                        'img' => $item['url'],
                        'md5' => $fileMd5,
                        'word' => $word,
                        'created_at' => date("Y-m-d H:i:s")
                    ];
                    $this->wordModel->insert($wordData);
                }

                $chapterData = [
                    "is_scan_img" => 2,
                    "updated_at" => date("Y-m-d H:i:s")
                ];
                $this->chapterModel->where('id', '=', $chapter->id)->update($chapterData);
                $start = $chapter->id;
            }
        }

        echo "所有已爬取章节扫描完毕\r\n";
    }

    public function handle2() {
        $wordRelation = $this->wordModel->pluck('word', 'img');
        // 获取第一条
        $chapterFirst = $this->chapterModel->where("website_id", '=',$this->webiste->id)
            ->where("is_spider", 1)->where('is_scan_img', 2)->first();
        $start = $chapterFirst ? $chapterFirst->id : 0;

        $handle = true;
        while($handle) {
            echo "从" . $start . "开始\r\n";
            $chapters = $this->chapterModel->where("website_id", '=',$this->webiste->id)
                ->where("is_spider", 1)
                ->where('is_scan_img', 2)
                ->where("id", ">=", $start)
                ->limit(500)->get();

            if (count($chapters) == 0) {
                $handle = false;
            }


            foreach ($chapters as $key => $chapter) {
                echo "开始处理章节《" . $chapter->chapter_name . "》\r\n";
                $extraWordList = $this->getExtraImgList($chapter->content);
                foreach ($extraWordList as $item) {
                    $chapter->content = str_replace($item, '', $chapter->content);
                }
                $wordList = $this->getImgList($chapter->content);
                foreach ($wordList as $key => $item) {
                    $chapter->content = str_replace($item['str'], $wordRelation[$item['url']], $chapter->content);
                }

                $chapter->content = str_replace('<noscript>', "", $chapter->content);
                $chapter->content = str_replace('</noscript>', "", $chapter->content);

                $data = [
                    "content" => $chapter->content,
                    "is_scan_img" => 3,
                    "updated_at" => date("Y-m-d H:i:s")
                ];
                $this->chapterModel->where("id",'=',$chapter->id)->update($data);
                echo "章节《" . $chapter->chapter_name . "》处理完毕\r\n";
                $start = $chapter->id;
            }
        }

    }

    public function getImgList($str) {
        preg_match_all("/<img data-cfsrc=\"(.*?)\".*?>/ism",$str,$match);

        $res =[];
        foreach($match[0] as $key => $item) {
            $res[]= [
                "str" => $item,
                "url" => $match[1][$key]
            ];
        }

        return $res;
    }

    public function getExtraImgList($str) {
        preg_match_all("/<noscript><img src=\".*?\".*?><\/noscript>/ism",$str,$match);
        return $match[0];
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

    public function getFilePath($fileName) {
        $savePath = 'public' . DIRECTORY_SEPARATOR . 'word' . DIRECTORY_SEPARATOR . $fileName;
        $path = base_path() . DIRECTORY_SEPARATOR . $savePath;
        return $path;
    }

    /**
     * 获取图片md5码
     */
    function getFileMd5($path){
        $md5 = null;

        if(file_exists($path)){
            $md5 = md5_file($path);
        }
        
        return $md5;
    }
}






