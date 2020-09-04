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

    public function scan($start,$end){
        echo "爬取开始\r\n";
        // $bookIdList = $this->bookModel->whereRaw('chapter_num >= current_page')->pluck('id')->toArray();
        $bookIdList = $this->bookModel->where('id','>=',$start)->where('id','<=',$end)->pluck('id')->toArray();

        if(!$bookIdList){
            echo "无可更新书籍\r\n";die;
        }

        echo "统计爬取书籍本数,共计：".count($bookIdList)."本\r\n\r\n";
        sleep(1);
        $bookList = array_chunk($bookIdList,$this->bookSize);

        foreach ($bookList as $bookKey => $bookValue) {

            $chapterListNum = $this->chapterModel->whereRaw("(source_content IS NULL OR source_content = '')")->whereIn('book_id',$bookValue)->count();

            if($chapterListNum == 0){
                // echo "未查找到章节信息,跳过\r\n\r\n";
                continue;
            }

            $chapterList = $this->chapterModel->whereRaw("(source_content IS NULL OR source_content = '')")->whereIn('book_id',$bookValue)->get();

            echo "分段爬取,统计该次爬取章节数,共计:". $chapterListNum ."章\r\n";
            foreach ($chapterList as $chapterKey => $chapterValue) {
                
                $content = $this->getChapterPageData($chapterValue->url);
                if($content){
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

    public function getChapterPageData($chapterHomeUrl){
        $this->chapterLastPage = 1;
        $this->handleChapterPageHome($chapterHomeUrl);
        return $this->handleChapterPage($chapterHomeUrl);
    }

    /**处理章节第一页数据 */
    public function handleChapterPageHome($url){
        /*获取章节第一页信息*/
        $url = $this->webUrl . $url;

        $pageHomeData = $this->getPageData($url);

        $content = $this->getContent($pageHomeData);

        /*获取本章最后分页*/
        $lastPage = $this->getChapterLastPage($content);

        if($lastPage){
            $this->chapterLastPage = $lastPage;
        }

        echo "扫描章节分页完成,共"."$this->chapterLastPage"."页：\r\n";
    }

    /**循环处理章节页面 */
    public function handleChapterPage($chapterHomeUrl){

        $chapterArray = explode('.',$chapterHomeUrl);
        $res = '';
        for ($i=1; $i <= $this->chapterLastPage; $i++) { 
            $chapterUrl = $this->webUrl . $chapterArray[0] . '_' .$i . '.html';
            $pageData = $this->getPageData($chapterUrl);
            $res = $res.$pageData;
            echo "第".$i."页数据爬取完成\r\n";
        }
        echo "章节数据爬取完成\r\n\r\n";
        return $res;
    }
}
