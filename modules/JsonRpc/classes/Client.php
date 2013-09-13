<?php
/**
 * @author Alexander Rodionov <avrodionov@rbc.ru>
 * $Id$
 */

namespace Miaox\JsonRpc;

class Client
{
    const VERSION_1_0 = 10;

    const VERSION_2_0 = 20;

    /**
     * @var string
     */
    protected $_serverUrl;

    /**
     * @var int
     */
    protected $_version;

    /**
     * @var bool
     */
    protected $_isExt;

    /**
     * @var resource
     */
    protected $_curl;

    /**
     * @var string
     */
    protected $_lastResponse;

    /**
     * @var string
     */
    protected $_login;

    /**
     * @var string
     */
    protected $_password;

    /**
     * @var int
     */
    protected $_authType;

    /**
     * @var int
     */
    protected $_timeout = 5;

    public function __construct( $serverUrl, $pVersion = self::VERSION_2_0 )
    {
        $this->_serverUrl = $serverUrl;

        if ( !in_array( $pVersion, array( self::VERSION_1_0, self::VERSION_2_0 ) ) )
        {
            throw new Exception( 'Invalid protocol version' );
        }
        $this->_version = $pVersion;
        $this->_isExt = $this->_version == self::VERSION_2_0;
    }

    public function __destruct()
    {
        isset( $this->_curl ) && curl_close( $this->_curl );
    }

    /**
     * @param string $pMethod
     * @param array $pParams
     * @param bool $pNotify
     * @throws Exception
     * @param bool $pNotify
     * @return mixed
     */
    public function call( $pMethod, array $pParams = array(), $pNotify = false )
    {
        if ( is_null( $this->_serverUrl ) )
        {
            throw new Exception( 'This is server JSON-RPC object: you can\'t call remote methods' );
        }
        $request = array(
            'method' => $pMethod,
            'params' => $pParams,
            'id' => md5( uniqid( null, true ) )
        );
        $this->_isExt && $request[ 'jsonrpc' ] = '2.0';
        $pNotify && $request[ 'id' ] = null;
        try
        {
            $this->_lastResponse = $json = $this->_postQuery( json_encode( $request ) );
            if ( !$pNotify )
            {
                $response = $this->_parseJson( $json );
                $this->_checkResponse( $response, $request[ 'id' ] );
                return $response[ 'result' ];
            }
        }
        catch ( Exception $e )
        {
            // hardcore :-)
            $e->setDebugCommand($this->callDebug($pMethod, $pParams, $pNotify));
            $e->setResponse($json);
            throw $e;
        }
    }

    /**
     * Возвращает коммандную строку вызова метода для дебага
     * @param string $pMethod
     * @param array $pParams
     * @param bool $pNotify
     * @return string
     */
    public function callDebug( $pMethod, array $pParams = array(), $pNotify = false )
    {
        $request = array(
            'method' => $pMethod,
            'params' => $pParams,
            'id' => md5( uniqid( null, true ) )
        );
        $this->_isExt && $request[ 'jsonrpc' ] = '2.0';
        $pNotify && $request[ 'id' ] = null;
        $post = json_encode( $request );
        $auth = '';
        if ( !empty( $this->_login ) )
        {
            $auth = sprintf( '--user %s:%s', $this->_login, $this->_password );
        }
        $command = sprintf( "curl -H 'Content-Type: application/json' -H 'Accept: application/json' -d '%s' %s %s | python -mjson.tool", $post, $auth, $this->_serverUrl );
        return $command;
    }

    public function notify( $pMethod, array $pParams = array() )
    {
        $this->call( $pMethod, $pParams, true );
    }

    public function getLastResponse()
    {
        return $this->_lastResponse;
    }

    protected function _postQuery( $pQuery )
    {
        $ch = isset( $this->_curl ) ? $this->_curl : curl_init();
        $options = array(
            CURLOPT_URL => $this->_serverUrl,
            CURLOPT_HEADER => 0,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $pQuery,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $this->_timeout
        );
        if ( !empty ( $this->_login ) )
        {
            $options[ CURLOPT_USERPWD ] = sprintf( '%s:%s', $this->_login, $this->_password );
            $options[ CURLOPT_HTTPAUTH ] = $this->_authType;
        }
        curl_setopt_array( $ch, $options );
        $response_json = curl_exec( $ch );
        if ( curl_errno( $ch ) )
        {
            throw new Exception( curl_error( $ch ), curl_errno( $ch ) );
        }
        if ( curl_getinfo( $ch, CURLINFO_HTTP_CODE ) != 200 )
        {
            throw new Exception( sprintf( 'Curl response http error code "%s"', curl_getinfo( $ch, CURLINFO_HTTP_CODE ) ) );
        }
        return $response_json;
    }

    protected function _parseJson( $pData )
    {
        $data = json_decode( $pData, true );
        if ( is_null( $data ) )
        {
            throw new Exception( 'Parse error', -32700 );
        }
        return $data;
    }

    protected function _checkResponse( $p, $id )
    {
        $v = is_array( $p );
        if ( $this->_isExt )
        {
            $v = $v && isset( $p[ 'jsonrpc' ] ) && $p[ 'jsonrpc' ] == '2.0';
            !isset( $p[ 'result' ] ) && $p[ 'result' ] = null;
            !isset( $p[ 'error' ] ) && $p[ 'error' ] = null;
        }
        $requireMap = array( 'result', 'error', 'id' );
        $keys = array_keys( $p );
        $v = array_diff( $requireMap, $keys );
        if ( !empty( $v ) )
        {
            $msg = sprintf( 'Invalid Response. Some keys not found (%s)', explode( ', ', $v ) );
            throw new Exception( $msg, -32600 );
        }
        elseif ( $p[ 'id' ] != $id )
        {
            $msg = sprintf( 'Invalid Response. Request id (%s) not equal (%s)', $id, $p[ 'id' ] );
            throw new Exception( $msg, -32500 );
        }

        if ( isset( $p[ 'error' ] ) )
        {
            if ( isset( $p[ 'error' ][ 'message' ] ) && isset( $p[ 'error' ][ 'code' ] ) && is_numeric( $p[ 'error' ][ 'code' ] ) )
            {
                throw new Exception( $p[ 'error' ][ 'message' ], $p[ 'error' ][ 'code' ] );
            }
            else
            {
                throw new Exception( $p[ 'error' ][ 'message' ] );
            }
        }
    }

    /**
     * @param int $timeout
     */
    public function setTimeout( $timeout )
    {
        $this->_timeout = $timeout;
    }

    /**
     * Авторизация
     * @param string $login логи
     * @param string $password пароль
     * @param int $type тип авторизации
     */
    public function setAuthInfo( $login, $password, $type = CURLAUTH_BASIC )
    {
        $this->_login = $login;
        $this->_password = $password;
        $this->_authType = $type;
    }
}
