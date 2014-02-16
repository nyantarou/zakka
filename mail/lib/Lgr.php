<?php

    class Lgr{
        //Lgr::debug
        public static function debug( $msg ){
            self::_outputLog( $msg , "debug" );
//            apache_note( "nyan" , $msg );
        }

        //Lgr::info
        public static function info( $msg ){
            self::_outputLog( $msg , "info" );
//            apache_note( "nyan" , $msg );
        }

        //Lgr::fatal
        public static function fatal( $msg ){
            self::_outputLog( $msg , "fatal" );
//            apache_note( "nyan" , $msg );
        }

        //log出力用にメッセージの形を整える
        private static function _fixFormat( $msg ){
            $msg = self::_isArray( $msg );
            $msg = self::_mbEncode( $msg );
            return $msg;
        }

        //logメッセージを出力
        private static function _outputLog( $msg , $logLevel ){
            $msg = self::_fixFormat( $msg );
            $logTime = "[" . date("Y/m/d H:i:s") . "]";
            $logLevel = "[" . $logLevel . "]";
            $msg = $logTime . $logLevel . $msg . "\n";
            error_log( $msg, 3, "/var/log/nyanlog/phperror.log" );
        }

        //logメッセージが配列の場合は文字列に変換
        private static function _isArray( $isArray ){
            if( is_array( $isArray ) ){
                return print_r( $isArray, true );
            }
            return $isArray;
        }

        //logメッセージの文字コードを変換
        private static function _mbEncode( $str ){
            return $str;
//            return mb_convert_encoding( $str, "EUC-JP", "auto");
//            return mb_convert_encoding( $str, "UTF-8", "auto");
        }

    }
