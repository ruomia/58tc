<?php

require_once('./redisFilter.php');

$filter = new RedisFilter();

$data = ['111', 'wqre', '111'];

foreach($data as $v) {
    
    if($filter->is_exists($v)){
        print("发现重复的数据: $v" );
    } else {
        $filter->save($v);
        print("保存数据: $v");
    }
}
// require_once('./Dao.php');
// $model = new Dao;
// $res = $model->db_exec("insert into room (title, room, infor, image) values(?,?,?,?)", ['1111', '2222', '33333', '44444']);
// print_r($res);