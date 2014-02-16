<?php
	class_exists( "GetApiCtrl" )
		|| require dirname( __FILE__ ) . "/GetApiCtrl.php";

	class textAnalysis{

		const APPID_TEXT_ANALYSIS_API = "dj0zaiZpPVNpcWJzNUhNTUw0SSZzPWNvbnN1bWVyc2VjcmV0Jng9OGI-";
		const NAME_ANALYSIS_API       = "textAnalysis";
		const API_URL_TEXT_ANALYSIS   = "http://jlp.yahooapis.jp/MAService/V1/parse";
		const API_PARAM_MA_FILTER     = 9;
		const API_PARAM_RESPONSE      = "surface,feature";
//		const API_PARAM_RESPONSE      = "surface,feature,pos,baseform";
		const API_PARAM_MA            = "ma";
//		const API_PARAM_MA            = "uniq";

		private $textData = "";
		private $analysisResult = array();
		protected static $essentialKey = array( "surface", "feature" );

		//日付取得正規表現パターン、優先度あり
		protected static $datePatternList = array(
					0 => "\d{1,2}月\d{1,2}日.*?(ころ|ごろ|頃)",//優先度1
					1 => "\d{1,2}月\d{1,2}日?",//優先度2
				);

		public function __construct( $data = "" ){
			$this->textData = $data;
			$this->api = GetApiCtrl::getInstance();
		}

		public function setTextData( $data = "" ){
			$this->textData = $data;
		}

		public function analysis(){
			$this->api = self::_setTextAnalysisApi( $this->api, $this->textData );
			$this->api->execute();
			$this->analysisResult = self::_parseTextAnalysisApi( $this->api->getResult( self::NAME_ANALYSIS_API ) );
		}

		public function getAnalysisData(){
			return $this->analysisResult;
		}

		private static function _setTextAnalysisApi( $apiObj, $data ){
			$apiParams = array(
					"appid"     => self::APPID_TEXT_ANALYSIS_API,
					"sentence"  => $data,
					"results"   => self::API_PARAM_MA,
					"ma_filter" => self::API_PARAM_MA_FILTER,
					"response"  => self::API_PARAM_RESPONSE,
					);
			$apiObj->setApiRequest( self::NAME_ANALYSIS_API, self::API_URL_TEXT_ANALYSIS, $apiParams );
			return $apiObj;
		}

		private static function _parseTextAnalysisApi( $data ){
			$parRes = array();
			$xml = simplexml_load_string( $data );
			if( $xml === false ){
				return $parRes;
			}

			for( $num = 0; isset( $xml->ma_result->word_list->word->{ $num } ); $num++ ){ 
				$record = array();
				$word = $xml->ma_result->word_list->word->{ $num };
				$record[ "surface" ] = isset( $word->surface )? (string) $word->surface : "";
				$record[ "feature" ] = isset( $word->feature )? (string) $word->feature : "";
				if( !self::checkEmptyField( $record, self::$essentialKey ) ){
					continue;
				}
				$parRes[] = $record;
			}

			return $parRes;
		}

		public static function checkEmptyField( $data, $essentialKey ){
			foreach( $essentialKey as $key ){
				if( !isset( $data[ $key ] ) || $data[ $key ] === "" ){
					return false;
				}
			}
			return true;
		}

		//かなりやっつけだけど、テキストの最初に現れる連続した地名情報を地域名として返却
		public static function getLocation( $analysisData ){
			$locationData = "";
			$locationFlag = false;
			foreach( $analysisData as $data ){
				$feature = explode( ",", $data[ "feature" ] );
				if( preg_match( "/地名|地名行政区分/", $feature[ 1 ] ) ){
					$locationData .= $data[ "surface" ];
					$locationFlag = true;
				}else if( $locationFlag === true ){
					break;
				}
			}
			return $locationData; 
		}

		//日付と思われるものを返却
		public static function getDateData( $text ){
			$dateData = "";
			//日付の数字が全角の場合があるため、半角化する
			$text = mb_convert_kana( $text, "n", "UTF-8" );
			for( $num = 0; isset( self::$datePatternList[ $num ] ); $num++ ){
				$format = self::$datePatternList[ $num ];
				$pattern = "/" . $format . "/";
				if( preg_match( $pattern, $text, $match ) ){
					$dateData = $match[ 0 ];
					break;
				}
			}
			return $dateData;
		}
	}






