<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use PhpParser\Node\Expr\Cast\String_;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\Throw_;

require __DIR__.'/../../../Lib/SimpleHtmlDom.php';

class CommonController extends Controller{

    protected $test = null;

    protected $currentDom = null;

    public function __construct()
    {
        
    }

    /**
     * 抓取网页
     * @param mixed $url
     * @param mixed $encode
     * @param mixed $isZip
     */
    public function getPageData($url, $website, $code = true){
        $config = ['http' => $website->http];
        if (count($website->header) > 0) {
            $config['header'] =  $website->header;
        }

        $lib = New \App\Lib\Common();
        $res = $lib->getData($url,$config);

        if(!$res){
            echo "页面请求失败，延时30秒尝试第二次请求\r\n";
            sleep(30);
            $res = $lib->getData($url,$config);
        }

        if(!$res){
            echo "页面请求失败，延时60秒尝试第三次请求\r\n";
            sleep(60);
            $res = $lib->getData($url,$config);
        }

        if(!$res){
            echo "页面请求失败，延时300秒尝试第四次请求\r\n";
            sleep(300);
            $res = $lib->getData($url,$config);
        }

        if(!$res){
            echo "页面请求失败,跳过该页面\r\n";
            return false;
        }

        // 自适应编码
        if (!$code) {
            return $res;
        }

        $code = mb_detect_encoding($res, array('GB2312','UTF-8', 'GBK'));
        if($code == 'EUC-CN' || $code == 'CP936'){
            $res = mb_convert_encoding($res, 'utf-8', 'gbk');
        }
        return $res;
    }


    /*获取页面内容*/
    public function getContent($str,$type = 'single'){
        $regexContent="/<div class=\"page-content font-large\".*?>.*?<\/center>/ism"; 
        if(preg_match_all($regexContent, $str, $matches)){

            if($type == 'single'){
                return $matches[0][0];
            }else{
                return $matches;
            }
        }else{
            return '';
        }
    }

    /*获取文本*/
    public function getTxt($str){
        $regex ="/<p>.*?<\/p>/ism"; 
        if(preg_match_all($regex, $str, $matches)){
            return $matches[0][0];
        }else{
            return '';
        }
    }

    /*获取文本中的图片列表*/
    public function getImgList($str){
        $regexImg = "/<img src.*?>/i";
        if(preg_match_all($regexImg,$str,$matches)){
            return $matches[0];
        }else{
            return [];
        }
    }

    /*获取章节分页信息*/
    public function getChapterLastPage($str){
        $regex ="/<a href=\"(.*?)\".*?>.*?<\/a>/i";
        if(preg_match_all($regex, $str, $matches)){
            $last = array_pop($matches[1]);
            $pageArray = explode('_',$last);
            $pageArray2 = explode('.',$pageArray[1]);
            return $pageArray2[0];
        }else{
            return '';
        }
    }

    /*获取页面内容*/
    public function getChapterContentFromDatabase($str){
        // if(strpos($str,'502') !== false){
        //     return false;
        // }
        $regexContent="/<div class=\"page-content font-large\".*?>.*?<\/div>/ism"; 
        if(preg_match_all($regexContent, $str, $matches)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 判断字符串是utf-8 还是gb2312
     * @param unknown $str
     * @param string $default
     * @return string
     */
    public function utf8_gb2312($str, $default = 'gb2312')
    {
        $str = preg_replace("/[\x01-\x7F]+/", "", $str);

        if (empty($str)) return $default;
        
        $preg = array(
            "gb2312" => "/^([\xA1-\xF7][\xA0-\xFE])+$/", //正则判断是否是gb2312
            "utf-8" => "/^[\x{4E00}-\x{9FA5}]+$/u",   //正则判断是否是汉字(utf8编码的条件了)，这个范围实际上已经包含了繁体中文字了
        );
        
        if ($default == 'gb2312') {
            $option = 'utf-8';
        } else {
            $option = 'gb2312';
        }
        
        if (!preg_match($preg[$default], $str)) {
            return $option;
        }
        $str = @iconv($default, $option, $str);
        
        //不能转成 $option, 说明原来的不是 $default
        if (empty($str)) {
            return $option;
        }

        return $default;
    }

    // 重构

    /**
     * 根据规则解析数据
     * @param mixed $pageData 带解析的页面
     * @param mixed $rules 解析规则
     * @return array
     */
    public function parseRule(string $pageData, array $rules = []) {
        $this->test = $pageData;
        $pageDom = str_get_html($pageData);

        $data = [];
        foreach($rules as $rule) {
            $dom = $pageDom;
            $singleData = [];
            foreach($rule['rule_list'] as $singleRule) {
                $this->handleSingleRule($singleData, $dom, $singleRule);
            }
            $data[$rule['tag']] = $singleData;
        }

        // 多个字段默认拼装，单个字段直接返回
        if (count($data) > 1) {
            $res = $this->arrOneToTwo($data);
        } else {
            $res = array_shift($data);
        }

        return $res;
    }

    /**
     * 处理单条规则
     */
    private function handleSingleRule(&$singleData, &$dom, $rule) {
        // 没指定元素就返回整个dom
        if ($rule['ele'] === null) {
            array_push($singleData, $dom->{$rule['atr']});
            return;
        }

        // 指定取第几个元素
        if (isset($rule["idx"])) {
            $eleList = $dom->find($rule['ele'], $rule["idx"]);

            // 查找失败了尝试拿第一个
            if (!isset($eleList->innertext)) {
                $eleList = $dom->find($rule['ele'], 0);
            }
        } else {
            $eleList = $dom->find($rule['ele']);
        }

        // remove时有可能是没有这个元素的
        if (isset($rule["idx"]) && !isset($eleList->innertext) && $rule['atr'] != "remove") {
            // $this->saveHtml($this->test);
            throw new \Exception("解析失败: 找不到指定元素. " . json_encode($rule), 501);
        }
        
        if (is_array($eleList) && count($eleList) == 0) {
            // $this->saveHtml($this->test);
            throw new \Exception("解析失败: 找不到指定元素. " . json_encode($rule), 501);
        }

        // 多步取值直接重置dom并返回
        if ($rule['atr'] == 'dom') {
            $dom = $eleList;
            return;
        }

        if ($rule['atr'] == "remove") {
            $dom->removeChild($eleList);
            return;
        }

        if (isset($rule["idx"])) {
            $singleData = $eleList->{$rule['atr']};
            return;
        }
    
        // 遍历取出目标属性
        foreach ($eleList as $ele) {
            if (!isset($ele->{$rule['atr']})) {
                throw new \Exception("解析失败: 对应属性不存在. " . json_encode($rule), 502);
            }
            array_push($singleData, $ele->{$rule['atr']});
        }

        // 处理替换
        if (isset($rule['replace'])) {
            foreach ($singleData as $key => $value) {
                $replace_param = explode('|', $rule['replace']);
                $singleData[$key] = str_replace($replace_param[0], $replace_param[1], $value);
            }
        }
    }


    /**
     * 把带下标的一维数组合成二维数组
     * @param mixed $arr
     * @return array
     */
    public function arrOneToTwo($arr)
    {
        // [
        //     "book_name" => ["1","2", "3"],
        //     "book_author" => ['111','222','333']
        // ];
        // array(3) {
        // [0]=>
        // array(2) {
        //     ["book_name"]=>
        //     string(1) "1"
        //     ["book_author"]=>
        //     string(3) "111"
        // }
        // [1]=>
        // array(2) {
        //     ["book_name"]=>
        //     string(1) "2"
        //     ["book_author"]=>
        //     string(3) "222"
        // }
        // [2]=>
        // array(2) {
        //     ["book_name"]=>
        //     string(1) "3"
        //     ["book_author"]=>
        //     string(3) "333"
        // }
        // }

        // 获取键
        $key_arr = array_keys($arr);

        // 获取长度
        $arr_length = 0;
        foreach ($arr as $key => $value) {
            if (count($value) > $arr_length) {
                $arr_length = count($value);
            }
        }

        // 循环赋值
        $res = [];
        for ($i=0; $i < $arr_length; $i++) { 
            $temp = [];
            foreach ($key_arr as $value) {
                $temp[$value] = $arr[$value][$i] ?? null;
            }
            $res[] = $temp;
        }
        return $res;
    }

    public function saveHtml($str) {
        $savePath = 'storage' . DIRECTORY_SEPARATOR . 'html.log';
        $path = base_path() . DIRECTORY_SEPARATOR . $savePath;

        file_put_contents($path, date("Y-m-d H:i:s") . "\r\n", FILE_APPEND);
        file_put_contents($path, $str, FILE_APPEND);
    }

    /**
     * 控制页面抓取时间间隔
     * @param mixed $webiste
     * @return void
     */
    public function sleepTime($webiste) {
        $time = isset($webiste->rule['spider_time']) ? $webiste->rule['spider_time'] : 0;
        echo "等待" . $time . "秒\r\n";
        sleep($time);
    }


    //     private function handleSingleRule(&$singleData, &$dom, $rule) {
    //     // 没指定元素就返回整个dom
    //     if ($rule['ele'] === null) {
    //         array_push($singleData, $dom->{$rule['atr']});
    //         return;
    //     }

    //     // 指定取第几个元素
    //     if (isset($rule["idx"])) {
    //         $eleList = $dom->find($rule['ele'], $rule["idx"]);

    //         // 查找失败了尝试拿第一个
    //         if (!isset($eleList->innertext)) {
    //             $eleList = $dom->find($rule['ele'], 0);
    //         }
    //     } else {
    //         $eleList = $dom->find($rule['ele']);
    //     }

    //     // remove时有可能是没有这个元素的
    //     if (isset($rule["idx"]) && !isset($eleList->innertext) && $rule['atr'] != "remove") {
    //         // $this->saveHtml($this->test);
    //         throw new \Exception("解析失败: 找不到指定元素. " . json_encode($rule), 501);
    //     }
        
    //     if (is_array($eleList) && count($eleList) == 0) {
    //         // $this->saveHtml($this->test);
    //         throw new \Exception("解析失败: 找不到指定元素. " . json_encode($rule), 501);
    //     }

    //     // 多步取值直接重置dom并返回
    //     if ($rule['atr'] == 'dom') {
    //         $dom = $eleList;
    //         return;
    //     }

    //     if ($rule['atr'] == "remove") {
    //         $dom->removeChild($eleList);
    //         return;
    //     }

    //     if (isset($rule["idx"])) {
    //         $singleData = $eleList->{$rule['atr']};
    //         return;
    //     }
    
    //     // 遍历取出目标属性
    //     foreach ($eleList as $ele) {
    //         if (!isset($ele->{$rule['atr']})) {
    //             throw new \Exception("解析失败: 对应属性不存在. " . json_encode($rule), 502);
    //         }
    //         array_push($singleData, $ele->{$rule['atr']});
    //     }

    //     // 处理替换
    //     if (isset($rule['replace'])) {
    //         foreach ($singleData as $key => $value) {
    //             $replace_param = explode('|', $rule['replace']);
    //             $singleData[$key] = str_replace($replace_param[0], $replace_param[1], $value);
    //         }
    //     }
    // }

    // 查找并处理单个元素
    public function findElement($dom, $rule) {
        $res = '';
        // 指定取第几个元素
        if (isset($rule["idx"])) {
            $domRes = $dom->find($rule['ele'], $rule["idx"]);

            // 查找失败了尝试拿第一个
            if (!isset($domRes->innertext)) {
                $domRes = $dom->find($rule['ele'], 0);
            }

            // 如果还是败了直接报错
            if (!isset($domRes->innertext)) {
                throw new \Exception("解析失败: 指定属性的索引不存在. " . json_encode($rule), 502);
            }
        }

        // 直接返回dom用于下一步处理
        if ($rule['atr'] == "dom") {
            $this->currentDom = $domRes;
            return null;
        }

        // 移除某些子元素   
        if ($rule['atr'] == "remove") {
            $this->currentDom->removeChild($domRes);
            return null;
        }

        $res = $domRes->{$rule['atr']};
        
        // 处理替换
        if (isset($rule['replace'])) {
            $replace_param = explode('|', $rule['replace']);
            $res = str_replace($replace_param[0], $replace_param[1], $res);
        }

        return $res;
    }

    // 查找复数的元素
    public function findElements($dom, $rule) {
        $domRes = $dom->find($rule['ele']);

        // 直接返回dom用于下一步处理
        if ($rule['atr'] == "dom") {
            $this->currentDom = $domRes;
            return [];
        }
        
        $res = [];
        foreach ($domRes as $key => $value) {
            $res[] = $value->{$rule['atr']};
        }
        return $res;
    }

    // 解析规则第二版(只支持一层循环处理)
    public function parseRuleV2($pageData, $rules) {
        $pageDom = str_get_html($pageData);

        $data = [];
        foreach($rules as $rule) {
            // 重置目标dom
            $this->currentDom = $pageDom;
            foreach($rule['rule_list'] as $singleRule) {
                $data[$rule['tag']] = $this->handleSingleRuleV2($singleRule);
            }
        }

        // 多个字段默认拼装，单个字段直接返回
        if (count($data) > 1) {
            $res = $this->arrOneToTwo($data);
        } else {
            $res = array_shift($data);
        }
        return $res;
    }

    // type 1取复数元素属性或者dom 2取单个元素属性或者dom  3循环dom取单个元素属性(先不支持嵌套)
    public function handleSingleRuleV2($rule) {
        $res = [];

        if ($rule['type'] == 1) {
            $res = $this->findElements($this->currentDom, $rule);
        }

        if ($rule['type'] == 2) {
            $this->currentDom = $this->findElement($this->currentDom, $rule);
        }

        if ($rule['type'] == 3) {
            $res = [];
            foreach ($this->currentDom as $domItem) {
                $res[] = $this->findElement($domItem, $rule);
            }
        }
        return $res;
    }
}

