<?php
	//DBのインスタンス作製クラス
	class DbFactory{

		const PDO_DSN_BASE = "mysql:dbname=%s;host=%s";

		protected static $instance = null;
		protected $dbh = array();

		protected static $pdoDefaultParams = array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_CLASS,
				);


		private function __construct(){}

		public static function getInstance(){
			if( self::$instance === null ){
				self::$instance = new self;
			}
			return self::$instance;
		}

		//dbオブジェクトを格納
		public function setDb( $dbname = "", $hostname = "", $username = "", $password = "", $key = "" ){
			if( $key === "" ){
				$key = $dbname;
			}
			$this->dbh[ $key ] = $this->getPdbInstance( $dbname, $hostname, $username, $password );
		}

		public function getDb( $key = "" ){
			if( $key === "" ){
				return $this->dbh;
			}
			return $this->dbh[ $key ];
		}

		protected function getPdbInstance( $dbname = "", $hostname="", $username = "", $password = "" ){
			$dsn = sprintf( self::PDO_DSN_BASE, $dbname, $hostname );
			try{
				return new PDO(
						$dsn,
						$username,
						$password,
						self::$pdoDefaultParams
					);
			}catch ( PDOException $e ){
				//pdo失敗時のエラー処理
				return null;
			}

		}
	}

