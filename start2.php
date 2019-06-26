<?php
require_once(__DIR__ . '/vendor/autoload.php');
require_once('./Dao.php');

use Beanbun\Beanbun;
use Beanbun\Lib\Db;
use Beanbun\Middleware\Parser;
use Beanbun\Middleware\Decode;
use Beanbun\Lib\Helper;

require_once(__DIR__ . '/vendor/autoload.php');

$beanbun = new Beanbun;
$beanbun->name = 'fangmama';
$beanbun->count = 3;
$beanbun->logFile = __DIR__ . '/fangmama_access.log';
$beanbun->max = 50;
$beanbun->interval = 10;
$beanbun->urlFilter = [
    '/https:\/\/suqian.58.com/ershoufang\/pn(\d*)/'
];
// 设置队列
$beanbun->setQueue('redis', [
    'host' => '127.0.0.1',
    'port' => '6379',
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
            //div[@class="house-title"]/h1/text()
            [
                'name' => 'title',
                'selector' => ['.house-title h1', 'text'],
                'repeated' => true,
            ],
            [
                'name' => 'room',
                'selector' => ['.house-cell .room', 'text'],
                'repeated' => true,
            ],
            [
                'name' => 'infor',
                'selector' => ['.house-cell .infor a', 'text'],
                'repeated' => true,
            ],
            //span[@class="c_ff552e"]/b/text()
            [
                'name' => 'price',
                'selector' => ['.c_ff552e b', 'text'],
                'repeated' => true,
            ],
            //ul[@class="f14"]/li[1]/span[2]/text()
            [
                'name' => 'method',
                'selector' => ['.f14 li:nth-child(1) span:nth-child(2)', 'text'],
                'repeated' => true,
            ],
            [
                'name' => 'type',
                'selector' => ['.f14 li:nth-child(2) span:nth-child(2)', 'text'],
                'repeated' => true,
            ],
            [
                'name' => 'storey',
                'selector' => ['.f14 li:nth-child(3) span:nth-child(2)', 'text'],
                'repeated' => true,
            ],
            //ul[@class="f14"]/li[4]/span[2]/a/text()
            [
                'name' => 'village',
                'selector' => ['.f14 li:nth-child(4) span:nth-child(2) a', 'text'],
                'repeated' => true,
            ],
            [
                'name' => 'region',
                'selector' => ['.f14 li:nth-child(5) span:nth-child(2) a', 'text'],
                'repeated' => true,
            ],
            [
                'name' => 'address',
                'selector' => ['.f14 li:nth-child(6) span:nth-child(2)', 'text'],
                'repeated' => true,
            ],
            //div[@class="house-chat-phone"]/span/text()
            [
                'name' => 'mobile',
                'selector' => ['.house-chat-phone span', 'text'],
                'repeated' => true,
            ],
            //div[@id="bigCustomer"]/p/a/text()
            [
                'name' => 'agent',
                'selector' => ['.agent-name.f16.pr a', 'text'],
                'repeated' => true,
            ],
            //ul[@id="leftImg"]/li/@data-src
            [
                'name' => 'images',
                'selector' => ['#leftImg li', 'data-src'],
                'repeated' => true,
            ]

        ]
    ]
];
//$beanbun->input_encoding = "UTF-8"; //输入编码
//$beanbun->output_encoding = "GBK"; //输出编码




$redis = new Redis(); 
$redis->connect('127.0.0.1', 6379); //连接Redis
$redis->select(2);//选择数据库2
$url_list = [];

// 每次最多访问100条
for($i = 0; $i < 100; $i++){
    $url = $redis->rpop('58tc_url_list');
    if(!$url) {
        break;
    }
    $url_list[] = $url ;
}
# 入口 需要是列表
$beanbun->seed = $url_list;
// $beanbun->seed = 'https://suqian.58.com/zufang/38417518758797x.shtml';
// $beanbun->pdo =  new Dao;

$beanbun->afterDownloadPage = function($beanbun) {
    
    preg_match("/https:\/\/suqian.58.com\/.*\/(\d.*)\.shtml/", $beanbun->url, $urls);
    // $beanbun->queue()->add($queue['url'], $queue['options']);die;
    // $beanbun->seed = $queue['url'];
    $model = new Dao;
    // 通过id判断数据是否重复
    $id = $urls[1];
    $resId = $model->db_getRow("select id from room where id = ? ", [$id]);

    if(!$resId) {
        $sql = "insert into room (id, title, type, method, image, price, storey, village, region, address, mobile, agent)  
            values(:id, :title, :type, :method, :image, :price, :storey, :village, :region, :address, :mobile, :agent); ";
        // print_r($beanbun);
        $res = [];
        foreach ($beanbun->data['template']['title'] as $key => $value) {
            $res['title'][] = myTrim($beanbun->data['template']['title'][$key]);

            $res['type'][] = myTrim($beanbun->data['template']['type'][$key]);

            $res['method'][] = myTrim($beanbun->data['template']['method'][$key]);

            $res['price'][] = myTrim($beanbun->data['template']['price'][$key]);

            $res['url'][] = myTrim($beanbun->data['template']['url'][$key]);

            $res['storey'][] = myTrim($beanbun->data['template']['storey'][$key]);
            $res['village'][] = myTrim($beanbun->data['template']['village'][$key]);
            $res['region'][] = myTrim($beanbun->data['template']['region'][$key]);
            $res['address'][] = myTrim($beanbun->data['template']['address'][$key]);
            $res['mobile'][] = myTrim($beanbun->data['template']['mobile'][$key]);
            $res['agent'][] = myTrim($beanbun->data['template']['agent'][$key]);

        }
        $mydecoder = $beanbun->decode; //解密类

        if($res) {
            $img = "";
            foreach($beanbun->data['template']['images'] as $key => $value) {
                // 补全url, 同时追加字符串
                $img .= "https:" . $value . ",";
            }

            foreach ($res['title'] as $key => $value) {

                // 插入之前判断标题是否重复
                $title = $mydecoder->deCodeByLuan($res['title'][$key]);
                $village = $mydecoder->deCodeByLuan($res['village'][$key]);
                
                // print($results);
        
                
                $sqlData = [];
                $sqlData[':id'] = $id;
                $sqlData[':title'] =   $title;
                $sqlData[':type'] =  $mydecoder->deCodeByLuan($res['type'][$key]);
                $sqlData[':method'] =  $mydecoder->deCodeByLuan($res['method'][$key]);
                $sqlData[':image'] =  $img;
                $sqlData[':price'] =  $mydecoder->deCodeByLuan($res['price'][$key]);
                $sqlData[':storey'] =  $mydecoder->deCodeByLuan($res['storey'][$key]);
                $sqlData[':village'] =  $village;
                $sqlData[':region'] =  $mydecoder->deCodeByLuan($res['region'][$key]);
                $sqlData[':address'] =  $mydecoder->deCodeByLuan($res['address'][$key]);
                $sqlData[':mobile'] =  $mydecoder->deCodeByLuan($res['mobile'][$key]);
                $sqlData[':agent'] =  $mydecoder->deCodeByLuan($res['agent'][$key]);
                $result = $model->db_exec($sql, $sqlData);
                if($result) {
                    echo "插入成功\n";
                }
                // echo $result . "\n";
                // print_r($data);
                // echo strlen($trimmed_str).'<br/>'; // Outputs: 28
                // mylog('正常标题:' . $res['title'][$key] . '--->>>> 解密文字：' . $re);
            }
        }
    } 


    
    
    
    //$beanbun->log("beanbun worker download {$beanbun->url} success------------------------!");
    
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
