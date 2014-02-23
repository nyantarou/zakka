<?php
	$workDir = dirname( __FILE__ );
	class_exists( "mailParser" )
		|| require $workDir . "/lib/mailParser.php";
	class_exists( "textAnalysis" )
		|| require $workDir . "/lib/textAnalysis.php";
	class_exists( "CrimeInfoMapper" )
		|| require $workDir . "/CrimeInfoMapper.php";


$mail = mailParser::getInstance();
//$mailData = file_get_contents( "/var/www/html/mail/mail/source/20140216-08:00:19.txt" );
$mailData = file_get_contents("php://stdin");
if( empty( $mailData ) ){
	exit();
}

//とりあえずメールのソースを保存しとく
$date = date( "Ymd-H:i:s", time() );
file_write( $mailData, $workDir . "/mail/source/" . $date . ".txt" );

//メールのタイトルと本文を取得
$mail->setMailData( $mailData );
$title = $mail->getMailData( "subject");
$mailComponent = $mail->getMailData( "component");
$mailText = $mailComponent[ "text" ];
//

//日時と場所を取得
$analysis = new textAnalysis( $mailText );
$analysis->analysis();
$locationData = textAnalysis::getLocation( $analysis->getAnalysisData() );
$dateData = textAnalysis::getDateData( $mailText );
//

$writeData = array(
		"[Location]" => $locationData,
		"[Date]"     => $dateData,
		"[Title]"    => $title,
		"[Text]"     => $mailText,
	);
//var_dump( $writeData );

$outputPath = $workDir . "/mail/%s.txt";
$outputPath = sprintf( $outputPath, $date );
file_write( $writeData, $outputPath );

//DB書き込み
$dbname = "mailDB";
$hostname = "localhost";
$username = "maildbuser";
$password = "nyandbpass";
$db = new DbFactory( $dbname, $hostname, $username, $password );
$cim = new CrimeInfoMapper( $db->getDb() );
$crimeInfoData = array(
		"Location" => $locationData,
		"Date"     => $dateData,
		"Title"    => $title,
		"Text"     => $mailText,
		);
//DB書き込み
$cim->insert( $crimeInfoData );


function file_write( $data, $path ){
//return true;
	$fp = fopen( $path, "w" );
	if( is_array( $data ) ){
		foreach( $data as $key => $value ){
			fwrite( $fp, $key . ":" . $value . "\n" );
		}
	}else{
		fwrite( $fp, $data );
	}
	fclose( $fp );
}


