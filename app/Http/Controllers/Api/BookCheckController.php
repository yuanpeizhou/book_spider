<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

class BookCheckController extends CommonController{

    protected $bookModel;

    protected $chapterModel;

    protected $checkSize = 100;

    protected $checkPage = 1;

    protected $total = 0;

    protected $book_name = null;

    protected $chapterPage = 0;

    protected $chapterSize = 5;

    protected $chapterTotal = 0;

    public function __construct()
    {
        $this->bookModel = New \App\Models\BookModel(); 

        $this->chapterModel = New \App\Models\ChapterModel(); 

        $this->total = $this->bookModel->count();

        $this->checkPage = ceil($this->total/$this->checkSize);
    }

    /**检查数据库所有书籍是否成功入库 */
    public function checkBookComplex(){
        echo "开始检查爬取数据\r\n";

        for ($i=0; $i < $this->checkPage; $i++) { 
            echo "处理书籍:". $i* $this->checkSize . '——' . ($i + 1) * $this->checkSize . "本书籍\r\n";
            $bookList = $this->bookModel->offset($i * $this->checkSize)->limit($this->checkSize)->get();

            foreach ($bookList as $key => $value) {
                $this->checkChapterList($value);
            }
        }
        
        echo "所有数据检查完毕\r\n";die;
    }


    /**
     * 检查书籍所有章节 
     * @param chapter book 书籍
     */
    public function checkChapterList($book){
        $this->book_name = $book->book_name;
        echo "开始检查《".$book->book_name."》,获取书籍章节\r\n";

        $this->chapterTotal = $this->chapterModel->where('book_id',$book->id)->count();

        $this->chapterPage = ceil($this->chapterTotal/$this->chapterSize);

        // if($book->id == 7){
        //     echo $this->chapterPage;exit;
        // }

        for ($i=0; $i < $this->chapterPage; $i++) { 
            $chapterList = $this->chapterModel->where('book_id',$book->id)
            ->offset($i * $this->chapterSize)->limit($this->chapterSize)
            ->select('id','chapter_order','url','source_content')->get();

            foreach ($chapterList as $key => $value) {
                $this->checkChapter($value);
            }
        }

        echo "《".$book->book_name."》检查完毕\r\n\r\n";
        // sleep(1);
    }

    /**
     * 检查单个章节是否正确爬取
     * @param index int 排序
     * @param chapter object 章节
     */
    public function checkChapter($chapter){
        echo "开始检查《".$this->book_name."》检查第". $chapter->chapter_order ."章数据\r\n";
        $is_true = true;
        if(!$chapter->source_content){
            // $chapter->source_content = null;
            $chapter->is_spider = 0;
            $chapter->img_is_scan = 0;
            $chapter->save();
            $is_true = false;
        }else{
            $is_check = $this->getChapterContentFromDatabase($chapter->source_content);

            if(!$is_check){
                // $chapter->source_content = null;
                $chapter->is_spider = 0;
                $chapter->img_is_scan = 0;
                $chapter->save();
                $is_true = false;

                echo "数据解析失败\r\n";
                if(!$is_check){
                    $this->checkFailLog($chapter);
                }
            }
        }
        if($is_true == true && $chapter->is_spider == 0){
            $chapter->is_spider == 1;
            $chapter->save();
        }

        echo "数据解析成功\r\n";
        // sleep(1);
    }

    public function checkFailLog($chapter){
        /**检查日志文件夹是否存在 */
        $dir = storage_path(). DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'check_book_logs';
        if(!is_dir($dir)){
            mkdir($dir);
        }

        $file_path = $dir . DIRECTORY_SEPARATOR . date("Y-m-d") . ".txt";
        $file = fopen($file_path ,'a');
        $log = "[" . date("Y-m-d H:i:s") . "]：".$chapter->id . "   " . $chapter->url . " 章节数据解析失败\r\n";
        fwrite($file,$log);
        fclose($file);
    }

}