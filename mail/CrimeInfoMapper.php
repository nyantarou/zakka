<?php
	$libBaseDir = dirname( __FILE__ );
	class_exists( "DbFactory" )
		|| require $libBaseDir . "/lib/DbFactory.php";
	class_exists( "DataMapper" )
		|| require $libBaseDir . "/lib/DataMapper.php";

	class CrimeInfoMapper extends DataMapper{
		protected $tableName = "crime_info";

		public function selectNewCrimeInfo( $limit = 10 ){
			$sql = array();
			$sql[] = sprintf( self::SQL_BASE_SELECT, "*", $this->tableName );
//			$sql[] = sprintf( self::SQL_BASE_ORDER_BY, "Id", "DESC" );
			$sql[] = sprintf( self::SQL_BASE_ORDER_BY, "InsDate", "DESC" );
			$sql[] = sprintf( self::SQL_BASE_LIMIT, $limit );
			if( $this->executeSql( $sql ) !== true ){
				return false;
			}
			return $this->fetchAllData();
		}

		public function selectCrimeInfo( $Id = 1 ){
			$sql = array();
			$sql[] = sprintf( self::SQL_BASE_SELECT, "*", $this->tableName );
			$sql[] = sprintf( self::SQL_BASE_WHERE, "Id=$Id" );
			if( $this->executeSql( $sql ) !== true ){
				return false;
			}
			return $this->fetchAllData();
		}
	}
/*
$dbname = "mailDB";
$hostname = "localhost";
$username = "maildbuser";
$password = "nyandbpass";
$db = DbFactory::getInstance( $dbname, $hostname, $username, $password );
$db->setDb( $dbname, $hostname, $username, $password );
$cim = new CrimeInfoMapper( $db->getDb( $dbname ) );
//var_dump( $cim->select( $target = "*" ) );


$txtDir = "/var/www/html/mail/mail/damp/";
$opnDir = opendir( $txtDir );
while( $fileName = readdir( $opnDir ) ){
	if( $fileName === "." | $fileName === ".." ){
		continue;
	}
	$dateParts = explode( "-", $fileName );
	$Ymd = $dateParts[ 0 ];
	$His = explode( ".", $dateParts[ 1 ] );
	$His = $His[ 0 ];
	$Y = substr( $Ymd, 0, 4 );
	$m = substr( $Ymd, 4, 2 );
	$d = substr( $Ymd, 6, 2 );
	$InsDate = $Y . "-" . $m . "-" . $d . " " . $His;
//var_dump( $InsDate );
	$fp = fopen( $txtDir . $fileName, "r" );
	if( $fp ){
		if( flock( $fp, LOCK_SH ) ){
			$line = "";
			while( !feof( $fp ) ){
				$line .= fgets( $fp );
			}
			flock( $fp, LOCK_UN );
		}
	}
	$tmp = explode( "[Location]:", $line );
	$tmp = explode( "[Date]:", $tmp[ 1 ] );
	$mLocation = $tmp[ 0 ];
	$tmp = explode( "[Title]:", $tmp[ 1 ] );
	$mDate = $tmp[ 0 ];
	$tmp = explode( "[Text]:", $tmp[ 1 ] );
	$mTitle = $tmp[ 0 ];
	$mText = $tmp[ 1 ];
	$mailData = array(
			"Location" => rtrim( $mLocation, "\n" ),
			"Date" => rtrim( $mDate, "\n" ),
			"Title" => rtrim( $mTitle, "\n" ),
			"Text" => $mText,
			"InsDate" => $InsDate
			);
//var_dump( $mailData );
var_dump( $cim->insert( $mailData ) );
}
closedir( $opnDir );
*/
