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
// $beanbun->seed = 'https://suqian.58.com/chuzu/';
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
            //img[@id="smainPic"]/@src
            [
                'name' => 'image',
                'selector' => ['#smainPic', 'src'],
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
            //ul[@class="agent-otherhouse-list"]/li/a/@href
            [
                'name' => 'room_url',
                'selector' => ['.agent-otherhouse-list li a', 'href'],
                'repeated' => true,
            ]

        ]
    ]
];
//$beanbun->input_encoding = "UTF-8"; //输入编码
//$beanbun->output_encoding = "GBK"; //输出编码`
$beanbun->seed = 'https://suqian.58.com/zufang/38463030679437x.shtml';

$beanbun->afterDownloadPage = function($beanbun) {
   
    // print($beanbun->url);
    $str = preg_match("/\/(.*)\.shtml/", $beanbun->url);
    // $str =;
    print_r($str);
  
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
