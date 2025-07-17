<?php

namespace App\Http\Controllers\Spider;
use App\Http\Controllers\Controller;
use App\Models\WebsiteModel;

require __DIR__.'/../../../Lib/SimpleHtmlDom.php';

class CommonController extends Controller{
    protected $currentDom = null;
    protected $spiderConfig = [
        "retry" => [
            [
                "sleep_time" => 30,
                "message" => "页面请求失败，延时30秒尝试第二次请求\r\n"
            ],
            [
                "sleep_time" => 60,
                "message" => "页面请求失败，延时60秒尝试第三次请求\r\n"                
            ],
            [
                "sleep_time" => 300,
                "message" => "页面请求失败，延时300秒尝试第四次请求\r\n"                
            ]
        ]
    ];

    /**
     * 抓取网页
     * @param mixed $url
     * @param mixed $website
     * @param mixed $code
     * @param mixed $repeat
     */
    public function getPageData($url, $website, $referer = false, $code = true, $repeat = 3){
        // 加载网站的配置
        $config = ['http' => $website->http, 'referer' => $referer];
        if (count($website->header) > 0) {
            $config['header'] =  $website->header;
        }

        $handle = true;
        $res = null;
        $count = 0;
        while($handle) {
            if ($count >= $repeat) {
                echo "页面请求失败,跳过该页面\r\n";
                break;
            }

            $lib = New \App\Lib\Common();
            $res = $lib->getData($url,$config);

            // 抓取成功，直接跳出循环
            if ($res) {
                break;
            }
            $this->saveHtml($res);
            echo $this->spiderConfig["retry"][$count]['message'];
            sleep($this->spiderConfig["retry"][$count]['sleep_time']);
            $count++;
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

    /**
     * 查找单个元素
     * @param mixed $dom
     * @param mixed $rule
     * @throws \Exception
     */
    public function findElement($dom, $rule) {
        $res = null;

        // 没有指定元素，意思就是不用查找了
        if (!$rule['ele']) {
            return $dom->{$rule['atr']};
        }

        if (!isset($rule["idx"])) {
            die("单个元素必须指定idx: " . json_encode($rule));
        }

        $domRes = $dom->find($rule['ele'], $rule["idx"]);

        // 查找失败了尝试拿第一个
        if (!isset($domRes->innertext)) {
            $domRes = $dom->find($rule['ele'], 0);
        }

        // 如果允许为空, 直接返回null
        if (isset($rule['not_exist']) && $rule['not_exist'] && !isset($domRes->innertext)) {
            return null;
        }

        // 移除某些子元素   
        if ($rule['atr'] == "remove") {
            // 查到了才移除
            if (isset($domRes->innertext)) {
                $this->currentDom->removeChild($domRes);
            }
            return;
        }

        // 如果还是败了直接报错
        if (!isset($domRes->innertext)) {
            throw new \Exception("解析失败: 指定属性的索引不存在. " . json_encode($rule), 502);
        }

        // 直接返回dom用于下一步处理
        if ($rule['atr'] == "dom") {
            $this->currentDom = $domRes;
            return;
        }

        $res = $domRes->{$rule['atr']};
        
        // 处理替换
        if (isset($rule['replace'])) {
            $replace_param = explode('|', $rule['replace']);
            $res = str_replace($replace_param[0], $replace_param[1], $res);
        }

        return $res;
    }

    /**
     * 查找复数元素
     * @param mixed $dom
     * @param mixed $rule
     * @return array
     */
    public function findElements($dom, $rule) {
        $domRes = $dom->find($rule['ele']);

        // 直接返回dom用于下一步处理
        if ($rule['atr'] == "dom") {
            $this->currentDom = $domRes;
            return [];
        }
        
        $res = [];
        foreach ($domRes as $key => $value) {
            $target = $value->{$rule['atr']};

            // 处理替换
            if (isset($rule['replace'])) {
                $replace_param = explode('|', $rule['replace']);
                $target = str_replace($replace_param[0], $replace_param[1], $target);
            }
            $res[] = $target;
        }
        return $res;
    }

    /**
     * 处理抓取规则
     * @param mixed $pageData
     * @param mixed $rules
     */
    public function parseRule($pageData, $rules) {
        $pageDom = str_get_html($pageData);

        $data = [];
        foreach($rules as $rule) {
            // 初始化当前dom
            $this->currentDom = $pageDom;

            // 处理解析规则
            foreach($rule['rule_list'] as $singleRule) {
                $data[$rule['tag']] = $this->handleSingleRule($singleRule);
            }
        }

        // 多个字段默认拼装，单个字段直接返回
        $res = null;
        if (count($data) > 1) {
            $res = $this->arrOneToTwo($data);
        } else {
            $res = array_shift($data);
        }
        return $res;
    }

    /**
     * 处理单条规则 type 1取复数元素属性或者dom 2取单个元素属性或者dom  3循环dom取单个元素属性或dom(不支持嵌套)
     * @param mixed $rule
     * @return array
     */
    public function handleSingleRule($rule) {
        $res = [];

        if ($rule['type'] == 1) {
            $res = $this->findElements($this->currentDom, $rule);
        }

        if ($rule['type'] == 2) {
            $res = $this->findElement($this->currentDom, $rule);
        }

        if ($rule['type'] == 3) {
            foreach ($this->currentDom as $domItem) {
                $res[] = $this->findElement($domItem, $rule);
            }
        }
        return $res;
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

    /**
     * 判断字符串是utf-8 还是gb2312
     * @param string $str
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

    public function saveHtml($str) {
        $savePath = 'storage' . DIRECTORY_SEPARATOR . 'html.log';
        $path = base_path() . DIRECTORY_SEPARATOR . $savePath;

        file_put_contents($path, date("Y-m-d H:i:s") . "\r\n", FILE_APPEND);
        file_put_contents($path, $str, FILE_APPEND);
    }


    /**
     * 一次性获取全部分页
     * @param string $pageData 页面数据
     * @param array $pageRule 分页解析规则
     * @param callable $function 回调函数
     * @return void
     */
    public function spiderPageByAll($website, string $pageData, array $pageRule, callable $function) {
        // 先把本页内容爬取了
        call_user_func($function, $pageData);

        // 解析获取分页列表
        $pageList = $this->parseRule($pageData, $pageRule);
        echo "共获取分页：" . count($pageList) . "\r\n";

        foreach ($pageList as $key => $value) {
            // 默认跳过第一个页面,因为上面已经处理了
            if ($key == 0) {
                continue;
            }

            $url = isset($website->relative_url) ? $website->relative_url . $value : $website->url . $value;
            $pageData = $this->getPageData($url, $website);
            // 执行回调
            call_user_func($function, $pageData);
        }
    }

    public function spiderPageByNext($website, string $pageData, array $pageRule, callable $function) {
        $currentPage = null;
        while (true) {
            // 执行回调
            call_user_func($function, $pageData);

            // 解析下一页
            $nextPage = $this->parseRule($pageData, $pageRule);
            if (!$nextPage || ($currentPage == $nextPage)) {
                echo "已到最后一页\r\n";break;
            }

            // 抓取下一页数据
            $currentPage = $nextPage;
            $this->sleepTime($website);
            $pageData = $this->getPageData($website->url . $nextPage, $website);
        }
    }
}