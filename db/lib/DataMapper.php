<?php
	class DataMapper{
		const SQL_BASE_SELECT         = 'SELECT %s FROM %s';
		const SQL_BASE_INSERT         = 'INSERT INTO %s ( %s ) VALUES ( %s )';
		const SQL_BASE_UPDATE         = 'UPDATE %s SET %s="%s"';
		const SQL_BASE_DELETE         = 'DELETE FROM %s';
		const SQL_BASE_WHERE          = 'WHERE %s';
		const SQL_BASE_ORDER_BY       = 'ORDER BY %s %s';
		const SQL_BASE_LIMIT          = 'LIMIT %s';
		const SQL_STATEMENT_SEPARATER = ' ';
		const SQL_ELEMENT_SEPARATER   = ',';

		protected $dbh = null;
		protected $tableName = "";

		public function __construct( $dbh ){
			$this->dbh = $dbh;
		}

		public function select( $target, $limit = 10 ){
			$target = self::joinArrayElement( $target );
			$sql = array();
			$sql[] = sprintf( self::SQL_BASE_SELECT, $target, $this->tableName );
			$sql[] = sprintf( self::SQL_BASE_LIMIT, $limit );
			if( $this->executeSql( $sql ) !== true ){
				return false;
			}
			return $this->fetchAllData(); 
		}

		public function insert( $targetSet ){
			$target = self::joinArrayElement( array_keys( $targetSet ) );
			$values = self::joinArrayElement( self::grantSingleQuotes( array_values( $targetSet ) ) );
			$sql = sprintf( self::SQL_BASE_INSERT, $this->tableName, $target, $values );
//var_dump( $sql );
			return $this->executeSql( $sql );
		}

		public function update( $sql = "" ){
			return $this->executeSql( $sql );

		}

		public function delete( $sql = "" ){
			return $this->executeSql( $sql );

		}

		//SQL実行ラッパーメソッド
		protected function executeSql( $sql ){
			//SQLの条件が複数なら連結させる
			$sql = self::joinArrayElement( $sql, self::SQL_STATEMENT_SEPARATER );
			return $this->executeSqlPdo( $sql );	
		}

		//PDO使用時のSQL実行メソッド
		protected function executeSqlPdo( $sql ){
			$this->stmt = $this->dbh->prepare( $sql );
			return $this->stmt->execute();
		}

		protected function fetchAllData(){
			return $this->stmt->fetchAll( PDO::FETCH_ASSOC );
		}

		//DB格納要素にシングルクォートを付与する
		public static function grantSingleQuotes( $elements ){
			if( !is_array( $elements ) ){
				return "'" . $elements . "'";
			}
			foreach( $elements as $key => $value ){
				$elements[ $key ] = "'" . $value . "'";
			}
			return $elements;
		}

		//連結メソッド
		public static function joinArrayElement( $elements, $separater = self::SQL_ELEMENT_SEPARATER ){
			if( !is_array( $elements ) ){
				return $elements;
			}
			return implode( $separater, $elements );
		}
	}
