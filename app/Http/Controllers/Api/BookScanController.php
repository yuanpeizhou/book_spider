<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

class BookScanController extends CommonController{

    /**
     * 网站模型
     */
    protected $webModel;

    /**
     * 书籍模型
     */
    protected $bookModel;

    /**
     * 章节模型
     */
    protected $chapterModel;

    /**
     * 书籍实例
     */
    protected $book;

    /**书籍章节数 */
    protected $dirLastPage;

    /**
     * 书籍爬取地址
     */
    protected $bookUrl;

    /**
     * 网页地址
     */
    protected $webUrl;

    /**
     *  一次扫描本数
     */
    protected $scanBookNum = 100;

    /**
     * 章节列表
     */
    protected $chapterList = [];

    protected $start = 16800;


    public function __construct($bookName = null,$bookUrl = null)
    {   
        $this->chapterModel = New \App\Models\ChapterModel();
        $this->bookModel = New \App\Models\BookModel();
        $this->webModel = New \App\Models\WebsiteModel();

        $this->webUrl = $this->webModel->find(1)->url;

    }   

    // public function insert(){

    // }

    // public function update(){

    // }

    public function scan(){
        
    } 

    /**扫描书籍章节 */
    public function chapterNumScan(){

        $total_book_count = $this->bookModel->where('id','>=',$this->start)->count();

        $pageTotal = ceil($total_book_count/$this->scanBookNum);

        for ($i= 0; $i < $pageTotal; $i++) { 
            request()->offsetSet('page', $i);
            $bookList = $this->bookModel->where('id','>=',$this->start)->simplePaginate($this->scanBookNum);

            foreach ($bookList as $key => $bookValue) {
                $this->getChapterLinkByBook($bookValue);

                /**写入书籍和章节数据 */
                DB::beginTransaction();
                try{
                    $bookChapterNum = count($this->chapterList);
                    $bookValue->chapter_num = $bookChapterNum;
                    $bookValue->save();

                    /**查看章节是否已写入 */
                    $chapterUrlList = [];
                    $chapterLibList = $this->chapterModel->where('book_id',$bookValue->id)->pluck('url')->toArray();
                    $order_start = count($chapterLibList);

                    foreach ($this->chapterList as $key => $value) {
                        if(!in_array($value,$chapterLibList)){
                            $chapterUrlList[] = $value;
                        }
                    }

                    $chapterList = [];
                    foreach ($chapterUrlList as $key => $value) {
                        $temp = [];
                        $temp['book_id'] = $bookValue->id;
                        $temp['url'] = $value;
                        $temp['chapter_order'] = $order_start + 1 + $key;
                        $temp['created_at'] = date("Y-m-d H:i:s",time());
                        $chapterList[] = $temp;
                    }

                    if($chapterList){
                        $this->chapterModel->insert($chapterList);
                    }

                    DB::commit();
                    echo "书籍《".$bookValue->book_name."》扫描成功\r\n\r\n";
                    // dd('123');
                }catch (\Exception $e) {
                    dd($e);
                    DB::rollBack();
                    echo "书籍《".$value->book_name."》信息入库失败\r\n";die;
                }
            }
            echo "成功录入100本书籍，休眠两秒钟\r\n\r\n";
            sleep(2);
        }
        echo "所有书籍录入完成\r\n\r\n";
    }

    /**
     * @param book object book实例
     */
    public function getChapterLinkByBook($book){
        $this->chapterList = [];
        $this->dirLastPage = [];
        $bookUrl = $this->webUrl . $book->url;
        
        echo "开始扫描《".$book->book_name."》\r\n";

        /**处理首页书籍 */
        $this->handlePageHome($bookUrl);

        /**处理剩余目录页面 */
        $this->handlePageDir($bookUrl);

        echo "成功获取书籍首页数据\r\n";
    }

    public function handlePageHome($bookUrl){

        /**获取首页信息 */
        $bookHomePage = $this->getPageData($bookUrl);

        /*获取首页章节信息并记录*/
        $page = $this->getChapterList($bookHomePage);
        $this->chapterList = array_merge($this->chapterList,$page);

        /**查找目录页最后一页 */
        $this->dirLastPage = $this->getDirLastPage($bookHomePage);

        echo "首页数据扫描完毕,共计".$this->dirLastPage."页\r\n";
    }

    public function handlePageDir($bookUrl){

        $tempArray = explode('/',$bookUrl);
        array_pop($tempArray);
        $chapterTempUrl = implode('/',$tempArray);

        if($this->dirLastPage > 1){
            for ($i=2 ; $i <= $this->dirLastPage ; $i++) { 
                echo "扫描".$i."页章节信息\r\n";
                $chapterUrl = $chapterTempUrl . '_' . $i . '/';
                $pageInfo = $this->getPageData($chapterUrl);
                $chapterList = $this->getChapterList($pageInfo);
                if(!is_array($chapterList)){
                    dd($pageInfo);
                }
                $this->chapterList = array_merge($this->chapterList,$chapterList);
            }
        }
        echo "剩余目录爬取完毕\r\n";
    }

    // public function get
}