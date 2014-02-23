<?php
	//DBのインスタンス作製クラス
	class DbFactory{

		const PDO_DSN_BASE = "mysql:dbname=%s;host=%s";

		protected static $pdoDefaultParams = array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_CLASS,
				);

		protected $dbh = null;

		public function __construct( $dbname = "", $hostname = "", $username = "", $password = "" ){
			$this->dbh = $this->getPdbInstance( $dbname, $hostname, $username, $password );
		}

		public function getDb(){
			return $this->dbh;
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

