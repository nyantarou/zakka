<?php

	$workDir = dirname( __FILE__ );
	class_exists( "CrimeInfoMapper" )
		|| require $workDir . "/CrimeInfoMapper.php";

	class CrimeInfoList{

		protected $dbname = "mailDB";
		protected $hostname = "localhost";
		protected $username = "maildbuser";
		protected $password = "nyandbpass";
		protected $cim = null;

		public function __construct(){
			$db = DbFactory::getInstance();
			$db->setDb( $this->dbname, $this->hostname, $this->username, $this->password );
			$this->cim = new CrimeInfoMapper( $db->getDb( $this->dbname ) );
		}

		public function viewCrimeInfoList(){
			$crimeList = $this->cim->selectNewCrimeInfo( 100 );
			//var_dump( $crimeList );
			echo '<h1>新着犯罪者情報</h1>';
			foreach( $crimeList as $crime ){
				echo '<li>';
				echo '<a href="?detail=' . $crime[ "Id" ] . '" >';
				echo '[' . $crime[ "InsDate" ] . ']:' . $crime[ "Title" ];
				echo '</a>';
				echo '</li>';
			}

		}

		public function viewCrimeInfoDetail( $Id ){
			$crime = $this->cim->selectCrimeInfo( $Id );
			if( empty( $crime ) ){
				$this->viewCrimeInfoList();
				exit();
			}
			$crime = $crime[ 0 ];
			echo '[Location]:' . $crime[ "Location" ];
			echo '<br>';
			echo '[Date]:' . $crime[ "Date" ];
			echo '<br>';
			echo '[Title]' . $crime[ "Title" ];
			echo '<br>';
			echo '[Text]:' . $crime[ "Text" ];
			echo '<br>';
			echo '<br>';
			echo '<a href="/crimeInfoList.php">新着犯罪情報一覧</a>';
		}
	}

$crimeInfoList = new CrimeInfoList();
if( isset( $_GET[ "detail" ] ) ){
	$crimeInfoList->viewCrimeInfoDetail( $_GET[ "detail" ] );
}else{
	$crimeInfoList->viewCrimeInfoList();

} 
