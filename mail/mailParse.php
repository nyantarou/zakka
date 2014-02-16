<?php
	//http://pear.php.net/manual/ja/package.mail.mail-mimedecode.example.php
	class_exists( "Mail_mimeDecode" )
		|| require "Mail/mimeDecode.php";
	

	class mailParser{

		//このクラスのインスタンスを保持
		private static $_instance = null;

		//メールデータ格納用
		private $_mailData    = array();

		//メールのヘッダーで取得する要素名配列
		private static $_mailHeaderKey = array( "from", "to", "subject" );

		//メールデコード用パラメータたち
		private static $_mailParams = array(
				"include_bodies" => true,
				"decode_bodies"  => true,
				"decode_headers" => true,
				"crlf"           => "\r\n",
				"input"          => null,
			);

		private function __construct(){}

		public function getInstance(){
			if( self::$_instance === null ){
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		//メールソースをセット
		public function setMailData( $mail ){
			self::$_mailParams[ "input" ] = $mail;
			$mailData = Mail_mimeDecode::decode( self::$_mailParams );
			$this->_mailData = $this->_parseMailData( $mailData );
		}

		//mailデータのパーサー
		private function _parseMailData( $mailData ){
			$record = array();
			foreach( self::$_mailHeaderKey as $mailHeaderKey ){
				$record[ $mailHeaderKey ] = $this::_getMailHeaderData( $mailData, $mailHeaderKey );
			}

			$record[ "component" ] = $this::_getMailComponent( $mailData );

			return $record;
		}

		//mailのヘッダーデータを返却
		private static function _getMailHeaderData( $mailData, $key ){
			return isset( $mailData->headers[ $key ] )? self::encode( $mailData->headers[ $key ] ) : "";
		}

		//基本的にはメールのbody部分のみ取得
		private static function _getMailComponent( $mailData ){
			$mime = $mailData->ctype_primary;
			$record = array();
			if( $mime === "text" ){
				$record = self::_getMailBody( $mailData );
			}else if( $mime === "multipart" ){
				foreach( $mailData->parts as $part ){
					if( !empty( $part->body ) ){
						 $record += self::_getMailBody( $part );
					}
				}
			}
			return $record; 
		}

		//メールのボディー部分のパース処理
		private static function _getMailBody( $mailData ){
			$record = array();
			if( $mailData->ctype_secondary === "plain" ){
				$record[ "text" ] = self::encode( $mailData->body );
			}else if( $mailData->ctype_secondary === "html" ){
				$record[ "html" ] = self::encode( $mailData->body );
			}
			return $record;
		}

		//メールパース後のデータを取得
		public function getMailData( $key = "" ){
			if( $key === "" ){
				return $this->_mailData;
			}else if( isset( $this->_mailData[ $key ] ) ){
				return $this->_mailData[ $key ];
			}else{
				return "";
			}
		}

		//メールのマルチバイト部分の文字コード変換
		public static function encode( $string, $encode = "UTF-8" ){
			//return mb_convert_encoding( $string, $encode, mb_detect_encoding( $string ) );
			return mb_convert_encoding( $string, $encode, "JIS" );
		}
	}

$mail = mailParser::getInstance();
//$mailData = file_get_contents("/var/www/html/mail/mailbody.txt");
$mailData = file_get_contents("php://stdin");

//とりあえずメールのソースを保存しとく
$date = date( "Ymd-H:i:s", time() );
file_write( $mailData, "/var/www/html/mail/mail/source/" . $date . ".txt" );

//メールのタイトルをファイル名にメール本文を保存
$mail->setMailData( $mailData );
$title = $mail->getMailData( "subject");
$outputPath = "/var/www/html/mail/mail/%s.txt";
$outputPath = sprintf( $outputPath, $title );
$mailComponent = $mail->getMailData( "component");
file_write( $mailComponent[ "text" ], $outputPath );

function file_write( $data, $path ){
	$fp = fopen( $path, "w" );
	fwrite( $fp, $data );
	fclose( $fp );
}
