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

    protected $start = 19;

    protected $pageSize = 20;


    public function __construct($bookName = null,$bookUrl = null)
    {   
        $this->chapterModel = New \App\Models\ChapterModel();
        $this->bookModel = New \App\Models\BookModel();
        $this->webModel = New \App\Models\WebsiteModel();

        $this->webUrl = $this->webModel->find(1)->url;

    }   

    /**扫描书籍章节 */
    public function chapterNumScan(){

        $total_book_count = $this->bookModel->where('id','>=',$this->start)->count();

        $pageTotal = ceil($total_book_count/$this->scanBookNum);
                        
        DB::beginTransaction();
        for ($i = 1; $i <= $pageTotal; $i++) { 
            request()->offsetSet('page', $i);
            $bookList = $this->bookModel->where('id','>=',$this->start)->simplePaginate($this->scanBookNum);

            foreach ($bookList as $key => $bookValue) {
                $bookInfo = $this->getChapterLinkByBook($bookValue);

                try{
                    $bookValue->last_update_date = date("Y-m-d");
                    $bookValue->save();
                    
                    /**对比章节数量，判断是否需要更新 */
                    if($bookInfo['is_spider']){
                        $bookValue->chapter_num = $bookInfo['chapterNum'];
                        $bookValue->save();
                    }else{
                        echo "该书籍未更新,跳过\r\n\r\n"; continue;
                    }
                    
                    

                    /**检查该章节是否写入 */
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
                        $chapterModel = New \app\Models\ChapterModel();
                        $chapterModel->insert($chapterList);
                    }

                    DB::commit();
                    echo "书籍《".$bookValue->book_name."》扫描成功\r\n\r\n";

                }catch (\Exception $e) {
                    var_dump($e);exit;
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
        $bookInfo = $this->handleBookInfo($bookUrl);

        /**检查是否更新 */
        if($book->chapter_num < $bookInfo['chapterNum']){
            echo "数据库当前章数：".$book->chapter_num.",需爬取：". ($bookInfo['chapterNum'] - $book->chapter_num) . "章\r\n";

            $satrtPage = ceil($book->chapter_num/$this->pageSize);
            $this->handlePageDir($bookUrl,$satrtPage);
            return ['is_spider' => true , 'chapterNum' => $bookInfo['chapterNum']];
        }
        return ['is_spider' => false , 'chapterNum' => $bookInfo['chapterNum']];
    }

    /**
     * 处理书籍信息，解析书籍章节数
     * @param bookUrl 书籍首页请求地址
     * @return [
     *  'isEnd' //是否只有一页
     *  'chapterNum' //书籍章节数量
     * ]
     */
    public function handleBookInfo($bookUrl){
        /**获取首页信息 */
        $bookHomePage = $this->getPageData($bookUrl);

        /*获取首页章节信息并记录*/
        $page = $this->getChapterList($bookHomePage);
        // $this->chapterList = array_merge($this->chapterList,$page);

        /**查找目录页最后一页 */
        $this->dirLastPage = $this->getDirLastPage($bookHomePage);

        

        /**只有一页 */
        if($this->dirLastPage == 1){
            $bookChapterNum = count($page);
            echo "书籍数据扫描完毕,共计".$this->dirLastPage."页,".$bookChapterNum."章\r\n";
            return [
                'isEnd' => true,
                'chapterNum' => $bookChapterNum
            ];
        }

        /**不止一页 */
        $endPageUrl = $this->webUrl . $this->getLastPage($bookHomePage);

        $bookEndPage = $this->getPageData($endPageUrl);

        $bookEndPageChapter = $this->getChapterList($bookEndPage);

        $bookChapterNum = count($bookEndPageChapter) + ($this->dirLastPage - 1)* $this->pageSize;

        echo "书籍数据扫描完毕,共计".$this->dirLastPage."页,".$bookChapterNum."章\r\n";

        return [
            'isEnd' => false,
            'chapterNum' => $bookChapterNum
        ];
    }

    public function handlePageDir($bookUrl,$start){

        $tempArray = explode('/',$bookUrl);
        array_pop($tempArray);
        $chapterTempUrl = implode('/',$tempArray);

        for ($i = $start ; $i <= $this->dirLastPage ; $i++) { 
            echo "扫描".$i."页章节信息\r\n";
            $chapterUrl = $chapterTempUrl . '_' . $i . '/';
            $pageInfo = $this->getPageData($chapterUrl);
            $chapterList = $this->getChapterList($pageInfo);
            if(!is_array($chapterList)){
                dd($pageInfo);
            }
            $this->chapterList = array_merge($this->chapterList,$chapterList);
        }
        echo "剩余目录爬取完毕\r\n";
    }

    /**获取并处理书籍最后一页 */
    public function handlePageEnd(){
    }

    /**
     * 获取目录最后的分页
     * @param str string 首页信息
     */
    public function getLastPage($str){
        $regex ="/<a class=\"endPage\" href=\"(.*?)\".*?>.*?<\/a>/i";
        if(preg_match_all($regex, $str, $matches)){

            return $matches[1][0];
        }else{
            return '';
        }
    }
}