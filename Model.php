<?php 
require_once('./Dao.php');

	class Model {
		//定义DAO类的属性
		private $dao;
		//增加构造方法
		public function __construct(){
			//给$pdo初始化
			$this->dao = Dao::getInstance();
		}


		//公共的查询的方法
		public function getAll($sql,$data=[]){
			
			// $sql = "select * from {$this->getTableName()}";
		 	return $this->dao->db_getAll($sql,$data);


		}
		/*
		 * 查询一行数据
		 */
		public function getRow($sql,$data=[]){


		 	return $this->dao->db_getRow($sql,$data);

		}

		public function exec(){
			return $this->dao->db_exec($sql,$data);
		}
		/*
		 * 定义静态方法，拼接表名
		 */
		
		public  function getTableName(){
			global $config;
			return $config['mysql']['prefix'].$this->table;
		}

		public function getFirstFeild($sql,$data=[]){
			return $this->dao->db_getFirstFeild($sql,$data);
		}
	}

 ?>