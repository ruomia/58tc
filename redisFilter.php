<?php

class RedisFilter{

    public function __construct()
    {
        $this->redis_host = '127.0.0.1';
        $this->redis_db = 0;
        $this->redis_port = 6379;
        $this->redis_key = "filter";
        $this->storage = $this->_get_storage();
    }

    public function _safe_data($data)
    {
        $hash_obj = $this->hash_func();
        $hash_obj.update($this->_safe_data($data));
        // $hash_value = hash_obj.hex
        return $hash_value;
    }

    public function _get_hash_value($data)
    {
        // $hash_obj = $this->hash_func();
        // $hash_obj.update($this->_safe_data($data));
        // $hash_value = $hash_obj
        $hash_value = md5($data);
        return $hash_value;
    }
    public function save($data)
    {
        # 根据data计算出对应的指纹进行存储
        $hash_value = $this->_get_hash_value($data);
        $this->_save($hash_value);
        return $hash_value;
    }

    public function _save($hash_value)
    {
        # 存储对应的hash值
        $this->storage->sadd('filter', $hash_value);
    }

    public function is_exists($data)
    {
        # 判断给定的数据对应的指纹是否存在
        $hash_value = $this->_get_hash_value($data);
        return $this->_is_exists($hash_value);
    }

    public function _is_exists($hash_value)
    {
        # 判断对应的hash值是否已经存在（交给对应的子类去继承）
        return $this->storage->sismember('filter', $hash_value);
    }

    public function _get_storage()
    {
        # 返回对应的一个存储的对象（交给对应的子类去继承）
        $redis = new Redis(); 
        $redis->connect('127.0.0.1', 6379); //连接Redis
        $redis->select(2);//选择数据库2 
        return $redis;       
        
    }
}