<?php

// $arr = []
// print(strlen('37777627751205x'));
$str = "https://suqian.58.com/zufang/38463030679437x.shtml";
// $str = "https://short.58.com/zd_p/ed0c38d1-0906-44bb-bbb7-78a166382a63/?target=fevamob-16-xgk_hvimob_36534236154564q-feykn&end=end&shangquan=shuyang";
//参数：preg_match(正则，字符串，结果集)
// preg_match("/https:\/\/suqian.58.com\/.*\/(\d.*)\.shtml/", $str, $str1);
preg_match("/https:\/\/suqian.58.com\/.*shtml/", $str, $str1);

    // $str =;
print_r($str1);