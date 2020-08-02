<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

class BookImgScanController extends CommonController{

    /**
     * 书籍model
     */
    protected $bookModel;

    /**
     * 章节model
     */
    protected $chapterModel;


    public function __construct($bookName = null , $bookUrl = null , $websiteId = 1)
    {   
        
        $this->bookModel = New \App\Models\BookModel();
        $this->chapterModel = New \App\Models\ChapterModel();

        // $this->wordModel = New \App\Models\WordModel();
        // $this->websiteModel = New \App\Models\WebsiteModel();
        // $this->bookName = $bookName;
        // $this->bookUrl = $bookUrl;
        // $this->websiteId = $websiteId;
        // $this->wordList  = $this->wordModel->pluck('url')->toArray();
    }

    /**
     * 书籍扫描
     */
    public function scan(){

        echo "开始扫描书籍图片\r\n\r\n";
        $bookIdList = $this->bookModel->select('id,book_name')->get()->toArray();

        echo "统计爬取书籍本数,共计：".count($bookIdList)."本\r\n\r\n";
        sleep(1);
        

        foreach ($bookIdList as $bookKey => $bookValue) {
            $chapterListNum = $this->chapterModel->where('img_is_scan',0)->where('book_id',$bookValue['id'])->count();

            if($chapterListNum == 0){
                echo "《".$bookValue['book_name']."》无图片需要扫描\r\n\r\n";
                continue;
            }

            $chapterList = $this->chapterModel->where('img_is_scan',0)->where('book_id',$bookValue['id'])->get();



            echo "开始扫描章节图片信息";
            foreach ($chapterList as $chapterKey => $chapterValue) {
                
                $content = $this->scanImg($chapterValue->source_content);
                $chapterValue->content = $content;
                $chapterValue->img_is_scan = 1;
                $chapterValue->save();

            }
            echo "分段爬取结束，休眠三秒钟后进行下次爬取\r\n\r\n";
        }
        echo "所有书籍都已爬取完毕\r\n\r\n";

    }

    /**扫描章节图片 */
    public function scanImg(){

    }

    /**
     * 书籍信息入库
     */
    public function bookSave(){
        /**处理首页书籍 */
        $this->handlePageHome();

        /**处理剩余目录页面 */
        $this->handlePageDir();

        /**写入书籍和章节数据 */
        DB::beginTransaction();
        try{
            $this->bookModel->website_id = $this->website->id;
            $this->bookModel->book_name = $this->bookName;
            $this->bookModel->url = $this->bookUrl;
            $this->bookModel->chapter_num = count($this->chapterList);
            $this->bookModel->current_page = 0;
    
            $this->bookModel->save();
    
            $chapterData = [];
            foreach ($this->chapterList as $key => $value) {
                $temp = [];
                $temp = [
                    'book_id' => $this->bookModel->id,
                    'chapter_order' => $key + 1,
                    'url' => $value,
                    'created_at' => date("Y-m-d H:i:s",time())
                ];
    
                $chapterData[] = $temp;
            }
    
            $this->chapterModel->insert($chapterData);

            DB::commit();
            $this->book = $this->bookModel;
            echo '书籍信息成功入库\r\n';
        }catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            echo '书籍信息入库失败\r\n';die;
        }
    }

    /**
     * 检查书籍是否在库
     */
    public function checkBook(){
        $book = $this->bookModel->where('url',$this->bookUrl)->first();
        if($book){
            $this->chapterList = $this->chapterModel->where('book_id',$book->id)->pluck('url');
            $this->book = $book;

            return true;
        }else{
            return false;
        }
    }


    // public function handlePageHome(){

    //     /**获取首页信息 */
    //     $bookHomePage = $this->getPageData($this->bookUrl);

    //     /*获取首页章节信息并记录*/
    //     $page = $this->getChapterList($bookHomePage);
    //     $this->chapterList = array_merge($this->chapterList,$page);

    //     /**查找目录页最后一页 */
    //     $this->dirLastPage = $this->getDirLastPage($bookHomePage);

    //     // return $bookHomePage;
    // }

    // public function handlePageDir(){
    //     if($this->dirLastPage > 1){
    //         for ($i=2 ; $i <= $this->dirLastPage ; $i++) { 
    //             echo "扫描".$i."页章节信息\r\n";
    //             $chapterUrl = $this->chapterUrl . '_' . $i . '/';
    //             $pageInfo = $this->getPageData($chapterUrl);
    //             $chapterList = $this->getChapterList($pageInfo);
    //             $this->chapterList = array_merge($this->chapterList,$chapterList);
    //         }
    //     }
    //     echo "目录爬取完毕,开始爬取书籍章节信息\r\n";
    // }

    // public function handleChapter(){
    //     $totalNum = $this->book->chapter_num;
    //     for ($i=0; $i < count($this->chapterList); $i++) { 

    //         $nowNum = $i + 1;

    //         if(!isset($this->chapterList[$i])){
    //             echo "第" . $nowNum. "章节信息读取错误"; die;
    //         }

    //         $value = $this->chapterList[$i];
            

    //         echo "开始爬取". $value ."章节数据,进度：" . $nowNum ."/". $totalNum ."\r\n";

    //         /*获取章节第一页信息*/
    //         $url = $this->website->url . $value;

    //         $chapterData = $this->getPageData($url);

    //         $content = $this->getContent($chapterData);

    //         /*获取本章最后分页*/
    //         $lastPage = $this->getChapterLastPage($content);

    //         if($lastPage){
    //             $this->chapterLastPage = $lastPage;
    //         }

    //         echo "扫描章节分页完成,共"."$this->chapterLastPage"."页：\r\n";

    //         /*循坏扫描图片*/
    //         $this->handlePage($value);

    //     }
    // }

    /**处理页面 */
    public function handlePage($chapter){
        $chapterArray = \explode('.',$chapter);
        $urlImgData = [];
        $imgData = [];

        for ($i=1; $i <= $this->chapterLastPage; $i++) { 
            $url = $this->website->url . $chapterArray[0] . '_' .$i . '.html';
            $pageData = $this->getPageData($url);
            $content = $this->getContent($pageData);
            $txt = $this->getTxt($content);
            $txt = str_replace('&nbsp;',"",$txt);
            $txt = str_replace('<noscript>',"",$txt);
            $txt = str_replace('</noscript>',"",$txt);
            $txt = str_replace('<br />',"\r\n",$txt);
            $txt = str_replace('<p>',"",$txt);
            $txt = str_replace('</p>',"",$txt);

            $imgArray = $this->getImgList($txt);

            foreach ($imgArray as $key => $value) {
                $exg = preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$value,$match);
                $wrordUrl = $this->website->url. $match[1];

                $urlImgData[] = $wrordUrl;
            }
            echo "扫描完第".$i."页图片\r\n";
        }

        $urlImgData = array_unique($urlImgData);

        foreach ($urlImgData as $key => $value) {
            $temp = [];
            $temp['website_id']  = $this->website->id;
            $temp['book_id']  = $this->book->id;
            $temp['book_name']  = $this->book->book_name;
            $temp['url']  = $value;
            $temp['tag']  = str_replace($this->website->url,'',$value);
            $temp['created_at']  = date("Y-m-d H:i:s",time());
            $imgData[] = $temp;
        }

        foreach ($imgData as $key => $value) {
            if(in_array($value['url'],$this->wordList)){
                unset($imgData[$key]);
            }
        }

        if($imgData){
            $res = $this->wordModel->insert($imgData);

            if($res){
                echo "章节图片扫描完毕\r\n";
            }else{
                echo "章节图片扫描失败\r\n";die;
            } 
        }

        echo "章节无图片扫描\r\n";
    }
}

        // $this->bookUrl =  $this->bookUrl ? $this->bookUrl : request()->bookUrl;
        // $this->bookName = $this->bookName ? $this->bookName : request()->bookName;

        // if(!$this->bookUrl || !$this->bookName){
        //     echo "缺少必要参数\r\n";die;
        // }

        // /**构造基本参数 */
        // $website = $this->websiteModel->find($this->websiteId);

        // if(!$website){
        //     echo "读取网站地址失败\r\n";die;
        // }

        // $this->website = $website;

        // $tempArray = explode('/',$this->bookUrl);
        // array_pop($tempArray);
        // $this->chapterUrl = implode('/',$tempArray);

        
        // if($this->checkBook()){
        //     echo "检索到书籍，开始扫描\r\n";
        // }else{
        //     echo "未查询到<<$this->bookName>>,开始扫描书籍\r\n";
        //     $this->bookSave();
        // }

        // $this->handleChapter();

        // echo "书籍所有图片扫描完毕\r\n";