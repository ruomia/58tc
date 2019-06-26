<?php 

	//主要作用：用来产生一个数据库操作对象，连接数据库等
	/*
	 * 单例类
	 * 三私一公
	 * 私：
	 * 1）私有静态属性
	 * 2）私有构造方法 防止无限制的new对象
	 * 3）私有克隆方法 防止无限制clone对象
	 * 公
	 * 1）对外提供那一个获取属性的方法
	 */
	class Dao {
		private static $dao = null;
		public static $error = "";
		//定义一个属性
		public $pdo;
		public function __construct(){
			try {
				$this->pdo = new PDO("mysql:host=localhost;dbname=58tc;port=3306;charset=utf8",'root',123456);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
                self::$error = $e->getMessage();
            }
		}

		public function __clone(){}

		public static function getInstance(){

			//判断对象是否是第一次创建，如果是第一次，new对象
			if(!self::$dao instanceof self){
				self::$dao = new self;
			}
			//如果不是第一次，直接返回静态属性
			return self::$dao;
		}

		function db_getAll($sql,$data=[]){
			try {
				//预处理
				$stmt = $this->pdo->prepare($sql);
				//如果预处理成功，则绑定数据
				if($stmt->execute($data)){
					return $stmt->fetchAll(PDO::FETCH_ASSOC);
				}
			} catch (PDOException $e) {
				self::$error = $e->getMessage();
				file_put_contents("Core/error.txt", self::$error);
				return false;
			}	
		}

		//执行 insert\upload\delete
		public function db_exec($sql,$data=[]){
			try {
                // $this->pdo->exec($sql);	
                //预处理
				$stmt = $this->pdo->prepare($sql);
				//如果预处理成功，则绑定数据
				if($stmt->execute($data)){
					return TRUE;
				}			
			} catch (PDOException $e) {
				//保留错误信息
				self::$error = $e->getMessage();
				//把错误信息添加到日志里面
				file_put_contents("Core/error.txt", self::$error);
				return false;
			}

		}
		//返回插入成功后的记录的id
		public function db_lastInsertId(){
			return $this->pdo->lastInsertId();
		}

		//获取一行
		public function db_getRow($sql,$data){
			try {
				//预处理
				$stmt = $this->pdo->prepare($sql);
				//如果预处理成功，则绑定数据
				if($stmt->execute($data)){
					return $stmt->fetch(PDO::FETCH_ASSOC);
				}
			} catch (PDOException $e) {
				self::$error = $e->getMessage();
				file_put_contents("Core/error.txt", self::$error);
				return false;
			}	
		}
		// getFirstFeild($sql,data) 获取查询结果的第一行第一列
		public function db_getFirstFeild($sql,$data=[]){	
			try {
				$stmt = $this->pdo->prepare($sql);
				$arr= array();
				if($stmt->execute($data)){
					$arr = $stmt->fetch(PDO::FETCH_NUM);
				}
				return $arr[0];
			} catch (PDOException $e) {
				self::$error = $e->getMessage();
				return false;
			}
		}
	}

 ?>