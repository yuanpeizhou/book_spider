<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

class ChapterScanController extends CommonController{

    protected $bookModel;

    protected $chapterModel;

    protected $webUrl;

    protected $bookSize = 5;

    protected $chapterLastPage = 1;

    public function __construct()
    {
        $this->webModel = New \App\Models\WebsiteModel();
        $this->bookModel = New \App\Models\BookModel();
        $this->chapterModel = New \App\Models\ChapterModel();

        $this->webUrl = $this->webModel->find(1)->url;
    }

    public function scan($start = null,$end = null){
        echo "爬取开始\r\n";
        if($start && $end){
            $bookIdList = $this->bookModel->where('id','>=',$start)->where('id','<=',$end)->pluck('id')->toArray();
        }else{
            $bookIdList = $this->bookModel->pluck('id')->toArray();
        }
        

        if(!$bookIdList){
            echo "无可更新书籍\r\n";die;
        }

        echo "统计爬取书籍本数,共计：".count($bookIdList)."本\r\n\r\n";
        sleep(1);
        $bookList = array_chunk($bookIdList,$this->bookSize);

        foreach ($bookList as $bookKey => $bookValue) {

            $chapterListNum = $this->chapterModel->whereRaw("is_spider = 0")->whereIn('book_id',$bookValue)->count();

            if($chapterListNum == 0){
                // echo "未查找到章节信息,跳过\r\n\r\n";
                continue;
            }

            $chapterList = $this->chapterModel->select('book.book_name','chapter.*')->join('book','book.id', '=' , 'chapter.book_id')
            ->whereRaw("is_spider = 0")->whereIn('book_id',$bookValue)->get();

            echo "分段爬取,统计该次爬取章节数,共计:". $chapterListNum ."章\r\n";
            foreach ($chapterList as $chapterKey => $chapterValue) {
                
                $content = $this->getChapterPageData($chapterValue);
                $is_check = $this->getChapterContentFromDatabase($content);

                $is_write = false;
                if(!$is_check){
                    echo "第一次爬取数据检验失败，开始第二次爬取\r\n";
                    $content = $this->getChapterPageData($chapterValue);
                    $is_check = $this->getChapterContentFromDatabase($content);
                    if(!$is_check){
                        echo "第二次爬取数据检验失败，开始第三次爬取\r\n";
                        $content = $this->getChapterPageData($chapterValue);
                        $is_check = $this->getChapterContentFromDatabase($content);
                        if(!$is_check){
                            echo "第三次爬取数据检验失败，跳过该章节\r\n\r\n";
                            $this->spiderFailLog($chapterValue);
                        }else{
                            $is_write = true;
                        }
                    }else{
                        $is_write = true;
                    }
                }else{
                    echo "数据校验成功,开始入库\r\n\r\n";
                    $is_write = true;
                }

                if($content && $is_write == true){
                    $chapterValue->source_content = $content;
                    $chapterValue->is_spider = 1;
                    $chapterValue->save();

                    $book = $this->bookModel->find($chapterValue->book_id);
                    $book->current_page = $book->current_page + 1;
                    $book->save();
                }
            }
            echo "分段爬取结束，休眠三秒钟后进行下次爬取\r\n\r\n";
            sleep(3);
        }
        echo "所有书籍都已爬取完毕\r\n\r\n";
    }
    
    public function getChapterPageData($chapter){
        $this->chapterLastPage = 1;
        $this->handleChapterPageHome($chapter);
        return $this->handleChapterPage($chapter);
    }

    /**处理章节第一页数据 */
    public function handleChapterPageHome($chapter){
        /*获取章节第一页信息*/
        $url = $this->webUrl . $chapter->url;

        $pageHomeData = $this->getPageData($url);

        $content = $this->getContent($pageHomeData);

        /*获取本章最后分页*/
        $lastPage = $this->getChapterLastPage($content);

        if($lastPage){
            $this->chapterLastPage = $lastPage;
        }

        echo "《".$chapter->book_name."》第".$chapter->chapter_order."分页扫描完毕,共"."$this->chapterLastPage"."页,id：". $chapter->id ."\r\n";
    }

    /**循环处理章节页面 */
    public function handleChapterPage($chapter){

        $chapterArray = explode('.',$chapter->url);
        $res = '';
        for ($i=1; $i <= $this->chapterLastPage; $i++) { 
            $chapterUrl = $this->webUrl . $chapterArray[0] . '_' .$i . '.html';
            $pageData = $this->getPageData($chapterUrl);
            /**页面抓取失败 */
            if(!$pageData){
                $res = null;break;
            }
            $res = $res.$pageData;
            echo "第".$i."页数据爬取完成\r\n";
        }
        echo "《".$chapter->book_name."》第". $chapter->chapter_order ."章节数据爬取完成,id：". $chapter->id ."\r\n";
        return $res;
    }

    public function spiderFailLog($chapter){
        /**检查日志文件夹是否存在 */
        $dir = storage_path(). DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'spider_chapter_fails_logs';
        if(!is_dir($dir)){
            mkdir($dir);
        }

        $file_path = $dir . DIRECTORY_SEPARATOR . date("Y-m-d") . ".txt";
        $file = fopen($file_path ,'a');
        $log = "[" . date("Y-m-d H:i:s") . "]：".$chapter->id . "   " . $chapter->url . " 章节数据爬取失败\r\n";
        fwrite($file,$log);
        fclose($file);
    }


}
