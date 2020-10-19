<?php
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

class LibScanController extends CommonController{

    protected $webKuUrl;

    protected $webHomeUrl;

    protected $websisteId = 1;

    protected $lastPage = 1;

    protected $shuKuUrl = '';

    protected $startPage = 1;

    public function __construct()
    {   
        $webModel = New \App\Models\WebsiteModel();

        $webUrl = $webModel->find(1)->url;
        $this->webLibUrl = $webUrl.'shuku/';
        $this->webHomeUrl = $webUrl;
        $this->bookModel = New \App\Models\BookModel();
    }

    /**
     * 扫描网站书籍
     */
    public function scan(){
        echo "开始爬取书库\r\n";
        $this->handlePageHome();
        $this->handleBookList();
        echo "书库数据爬取完毕\r\n\r\n";
    }

    /**处理第一页数据 */
    public function handlePageHome(){
        echo "开始扫描首页信息\r\n";
        /**获取书籍信息 */
        $homePageData = $this->getPageData($this->webLibUrl);


        $pageContent = $this->getLibContent($homePageData);

        $endPageData = $this->getLibLastPage($homePageData);

        $this->shuKuUrl = $endPageData['shuKuUrl'];
        $this->lastPage = $endPageData['lastPage'];

        $this->bookSave($pageContent);

        echo "首页成功入库\r\n";
    }

    /**循环处理剩余页面 */
    public function handleBookList(){
        for ($i = ($this->startPage ? $this->startPage : 2); $i <= $this->lastPage; $i++) { 
            echo "开始爬取第". $i ."页书籍\r\n";
            $url = $this->webHomeUrl . $this->shuKuUrl . '-' . $i . '.html';
            $this->handleLibPage($url);
            echo "成功爬取第". $i. "/" . $this->lastPage ."页书籍\r\n\r\n";
        }
    }

    /**
     * 处理单页面
     */
    public function handleLibPage($url){
        $pageData = $this->getPageData($url);
        
        $pageContent = $this->getLibContent($pageData);
        
        $this->bookSave($pageContent,true);
    }

    /**
     * 书籍信息入库
     */
    public function bookSave($bookList,$test = false){
        $bookData = [];

        foreach ($bookList as $key => $value) {
            $data = $this->getBookInfo($value);

            $temp = [];
            $temp['website_id'] = $this->websisteId;
            $temp['book_name'] = $data['book_name'];
            $temp['author_name'] = $data['author_name'];
            $temp['url'] = $data['book_url'];
            $temp['last_update_date'] = $data['last_update_date'];
            $temp['words'] = $data['boook_words'];
            

            /**查询该书籍是否是在库书籍 */
            $book = $this->bookModel->where('book_name',$data['book_name'])->first();
            if($book){
                $temp['updated_at'] = date("Y-m-d H:i:s",time());
                $temp['last_scan_date'] = date("Y-m-d H:i:s",time());
                $book->fill($temp);
                $book->save();
            }else{
                $temp['created_at'] = date("Y-m-d H:i:s",time());
                $bookModel = New \app\Models\BookModel();
                $bookModel->insert($temp);
            }
        }
    }

}

// https://www.lzlib.com/go.htm?c=xin_wen_zhong_xin&url=ltgk2&pageIndex=28