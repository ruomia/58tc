<?php
require_once(__DIR__ . '/vendor/autoload.php');
require_once('./redisFilter.php');
use Beanbun\Beanbun;
use Beanbun\Lib\Db;
use Beanbun\Middleware\Parser;
use Beanbun\Middleware\Decode;
use Beanbun\Lib\Helper;

require_once(__DIR__ . '/vendor/autoload.php');

$beanbun = new Beanbun;
$beanbun->name = 'fangmama';
$beanbun->count = 3;
$beanbun->seed = 'https://shuyang.58.com/zufang';
$beanbun->logFile = __DIR__ . '/fangmama_access.log';
$beanbun->max = 1000;
$beanbun->interval = 10;
$beanbun->urlFilter = [
    // '/https:\/\/suqian.58.com/ershoufang\/pn(\d*)/',
    '/https:\/\/shuyang.58.com\/zufang\/pn(\d*)/',
    // 'https://shuyang.58.com/lysczhen/zufang/pn3/'
];
// 设置队列
$beanbun->setQueue('memory', [
    'host' => '127.0.0.1',
    'port' => '2217',
    'algorithm' => 'breadth'
]);


$beanbun->middleware(new Parser);
$beanbun->middleware(new Decode);

$beanbun->fields = [
    [
        'name' => 'title',
        'selector' => ['title', 'text']
    ],
    [
        'name' => 'template',
        'children' => [
            //li[@class="house-cell"]/div[@class="img-list"]/a/@href
            [
                'name' => 'url',
                'selector' => ['.house-cell .img-list a', 'href'],
                'repeated' => true,
            ]
        ]
    ]
];
//$beanbun->input_encoding = "UTF-8"; //输入编码
//$beanbun->output_encoding = "GBK"; //输出编码

// echo $redis->get("testKey");//输出value
$beanbun->filter = new RedisFilter();

$beanbun->afterDownloadPage = function($beanbun) {
    $redis = new Redis(); 
    $redis->connect('127.0.0.1', 6379); //连接Redis
    $redis->select(2);//选择数据库2

    $urls = $beanbun->data['template']['url'];
    print_r($urls);
    foreach ($urls as $key => $value) {
        // 匹配正则，获取正确的url
        preg_match("/https:\/\/.*.58.com\/.*shtml/", $value, $preg_url);
        if($preg_url) {
            $uri = $preg_url[0];
            echo $uri . "\n";
            if($beanbun->filter->is_exists($uri)){
                print("发现重复的数据\n" );
            } else {
                $beanbun->filter->save($uri);
                print("保存数据\n");
                $redis->lpush( "58tc_url_list" , $uri); //设置测试key
            }
        }
        
    }
    //$mydecoder->deCodeByUnicode('&#x9a4b;&#x9476;&#x9fa4;&#x9fa4;'); //调用方法2
    //$mydecoder->deCodeByLuan('餼室龒厅龒卫鸺鸺')////调用方法1
};
$beanbun->start();

function mylog($string = '', $APPEND = true, $file = 'test') {
    return file_put_contents(__DIR__ . '/collectTest/' . $file . '.log', $string . PHP_EOL, $APPEND ? FILE_APPEND : false);
}

function myTrim($str) {
    $search = array(" ", "　", "\n", "\r", "\r\n", "\t");
    $replace = array("", "", "", "", "");
    return str_replace($search, $replace, $str);
}
