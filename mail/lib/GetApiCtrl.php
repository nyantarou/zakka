<?php

/**
* 
* GetApiCtrlクラス 
*
*/

class GetApiCtrl{

    // {{{ constants
    const QUERY_ENC_TYPE_RFC1738 = "PHP_QUERY_RFC1738";
    const QUERY_ENC_TYPE_RFC3986 = "PHP_QUERY_RFC3986";
    // }}}

    // {{{ property
    private static $_instance = null;
    private  $_results = array();
    private  $_requests = array();
    private  $_errorinfo = array();
    // }}}

    // {{{ private function __construct(){}
    /**
     * コンストラクタ
     * Singletonにしたいのでprivate 
     *
     */
    private function __construct(){}
    // }}}

    // {{{ public static function getInstance(){
    /**
     * GetApiCtrlクラスのインスタンス返却用メソッド
     *
     * $return object self::$_instance GetApiCtrlクラスのインスタンス
     */
    public static function getInstance(){
        if( !isset( self::$_instance ) ){
            self::$_instance = new GetApiCtrl();
        }
        return self::$_instance;
    }
    // }}}

    // {{{ public function setApiRequest( $key, $api_base_url, $params=array(), $query_enc_type = self::QUERY_ENC_TYPE_RFC1738 ){
    /**
     * APIのリクエスト設定用メソッド
     *
     * $param string $key リクエスト識別用キー
     * $param string $api_base_url APIのベースURL
     * $param string $query_enc_type queryパラメータのエンコードタイプ 
     * $param array $params APIリクエスト時の付与パラメータ
     *
     */
    public function setApiRequest( $key, $api_base_url, $params=array(), $query_enc_type = self::QUERY_ENC_TYPE_RFC1738 ){
        $request_url = $api_base_url;
        if( !empty( $params ) ){
            if( $query_enc_type === self::QUERY_ENC_TYPE_RFC3986 ){
                //半角スペースは「%20」になる
                $request_url = $api_base_url . "?" . self::httpBuildQueryRFC3986( $params );
            }else{ 
                //半角スペースは「+」になる
                $request_url = $api_base_url . "?" . http_build_query( $params );
            }
        }
        $this->_requests[ $key ] = $request_url;
    }
    // }}}

    // {{{ public function getResult( $key = "" ){
    /**
     * APIのレスポンス取得用メソッド
     *
     * $param string $key リクエスト識別用キー
     *
     * $return array $this->_result APIのレスポンス
     */
    public function getResult( $key = "" ){

        if( $key === "" ){
            return $this->_results;
        }
        return $this->_results[ $key ];
    }
    // }}}

    // {{{ public function getErrorInfo( $key = "" ){
    /**
     * APIのエラー情報取得用メソッド
     *
     * $param string $key リクエスト識別用キー
     *
     * $return array $this->_errorinfo APIのエラー情報
     */
    public function getErrorInfo( $key = "" ){

        if( $key === "" ){
            return $this->_errorinfo;
        }
        return $this->_errorinfo[ $key ];
    }
    // }}}

    // {{{ public static function execute()
    /**
     *
     * Curlマルチ実行メソッド
     *
     */
    public function execute() {

        $multi_handle = curl_multi_init();
        $handle_list  = array();
        $results = array();
        $error_info = array();
        // Curlオプション セット
        foreach( $this->_requests as $req => $val ) {
            $handle_list[ $req ] = curl_init();

            curl_setopt( $handle_list[ $req ], CURLOPT_URL, $val);
            curl_setopt( $handle_list[ $req ], CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $handle_list[ $req ], CURLOPT_HEADER, 1 );
            curl_setopt( $handle_list[ $req ], CURLOPT_TIMEOUT, 30 );
            curl_setopt( $handle_list[ $req ], CURLOPT_CONNECTTIMEOUT, 30 );
            curl_multi_add_handle( $multi_handle, $handle_list[ $req ] );
        }

        // 実行
        $running = 0;
        do {
            $mrc = curl_multi_exec( $multi_handle, $running );
        } while( $mrc == CURLM_CALL_MULTI_PERFORM );

        while( $running && $mrc == CURLM_OK ) {
            if( curl_multi_select( $multi_handle ) != -1 ) {
                do {
                    $mrc = curl_multi_exec( $multi_handle, $running );
                }while( $mrc == CURLM_CALL_MULTI_PERFORM );
            }
        }

        // 結果を取得し ハンドルを閉じる
        foreach( $handle_list as $req => $curl_handle ) {

            // エラーがあったかどうか
            // TODO
            if ( ( $error_msg = curl_error( $curl_handle ) ) !== "" ) {
                error_log( sprintf( "*** Curl Error = [ %s ] : [ %s ]", $req, $error_msg ) );
                $error_info[$req]['msg'] = $error_msg;
                $error_info[$req]['no'] = curl_errno($curl_handle);
            }

            // Curl 情報を格納
            $content = curl_multi_getcontent( $curl_handle );
            $header_size = curl_getinfo($curl_handle, CURLINFO_HEADER_SIZE);
            // 結果取得
            $results[ $req ] = substr( $content, $header_size );

            curl_multi_remove_handle( $multi_handle, $curl_handle );

        }

        curl_multi_close( $multi_handle);

        $this->_results = $results;
        $this->_errorinfo = $error_info;

        //リクエストを初期化します。
        $this->_requests = array();
    }

    // }}}

    // {{{ public static function httpBuildQuery( $params, $separator = '&' ){
    /**
     * httpBuildQueryRFC3986 
     * 
     * ネイティブのhttp_build_queryは、半角スペースをデフォルトで「+」に変換してしまう。 
     * 第四引数のenc_typeで「%20」に変換するようにエンコードタイプが変更できるが、この仕様は 
     * PHP5.4からの様なので、それまでこの関数で対応 
     * 
     * @param array $params 
     * @param string $separator 
     * @static
     * @access public
     * @return string 
     */
    public static function httpBuildQueryRFC3986( $params, $separator = '&' ){
        $query = array();
        foreach( $params as $key => $value ){
            $query[] = sprintf( '%s=%s', $key, rawurlencode( $value ) );
        }
        return implode( $separator, $query );
    }
    // }}}

}

