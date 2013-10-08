<?php
/**
 * @author Alexander Rodionov <avrodionov@rbc.ru>
 * $Id$
 */

namespace Miaox\JsonRpc;

class Client
{
    /**
     * @var string
     */
    protected $_serverUrl;

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

    /**
     * If true using multiple requests
     * @var bool
     */
    protected $_multi = false;

    /**
     * Запросы
     * @var array
     */
    protected $_requests = array();

    public function __construct( $serverUrl )
    {
        $this->_serverUrl = $serverUrl;
    }

    public function __destruct()
    {
        isset( $this->_curl ) && curl_close( $this->_curl );
    }

    /**
     * @param string $pMethod
     * @param array $pParams
     * @param string|null $id
     * @throws Exception
     * @return array
     */
    public function call( $pMethod, array $pParams = array(), $id = null )
    {
        $request = $this->_formatRequest( $pMethod, $pParams, $id );
        if ( $this->_multi )
        {
            $id = $request[ 'id' ];
            if ( !empty( $this->_requests[ $request[ 'id' ] ] ) )
            {
                throw new Exception( sprintf( "Method with the id = '%s' already exist", $id ) );
            }
            $this->_requests[ $id ] = $request;
        }
        else
        {
            try
            {
                $this->_lastResponse = $json = $this->_postQuery( json_encode( $request ) );
                $response = $this->_parseJson( $json );
                $this->_checkResponse( $response, $request[ 'id' ] );
                return $response[ 'result' ];
            }
            catch ( Exception $e )
            {
                // hardcore :-)
                $e->setDebugCommand( $this->callDebug( $pMethod, $pParams, $pNotify ) );
                $e->setResponse( $json );
                throw $e;
            }
        }
    }

    /**
     * Returns a unix command for debugging
     * @param string $pMethod
     * @param array $pParams
     * @param bool $pNotify
     * @return string
     */
    public function callDebug( $pMethod, array $pParams = array(), $id = null )
    {
        $request = $this->_formatRequest( $pMethod, $pParams, $id );
        return $this->_postQueryDebug( json_encode( $request ) );
    }

    public function notify( $pMethod, array $pParams = array() )
    {
        $request = $this->_formatRequest($pMethod, $pParams, null);
        $this->_postQuery(json_encode($request));
    }

    public function getLastResponse()
    {
        return $this->_lastResponse;
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

    public function startBatch()
    {
        $this->resetBatch();
        $this->_multi = true;
    }

    public function resetBatch()
    {
        $this->_multi = false;
        $this->_requests = array();
    }

    public function callBatch()
    {
        if ( !$this->_multi )
        {
            throw new Exception( "You need to call startBatch before using this method" );
        }

        if ( empty( $this->_requests ) )
        {
            throw new Exception( "Empty batch" );
        }

        $requests = array_values( $this->_requests );
        $this->_lastResponse = $json = $this->_postQuery( json_encode( $requests ) );
        $response = $this->_parseJson( $json );
        $ret = array();
        for ( $i = 0; $i < count( $requests ); $i++ )
        {
            $request = $requests[ $i ];
            try
            {
                $this->_checkResponse( $response[ $i ], $request[ 'id' ] );
                $ret[ $request[ 'id' ] ] = $response[ $i ][ 'result' ];
            }
            catch ( Exception $e )
            {
                $e->setDebugCommand($this->_postQueryDebug(json_encode($requests)));
                $e->setResponse($this->_lastResponse);
                $ret[ $request[ 'id' ] ] = $e;
            }
        }
        return $ret;
    }

    protected function _formatRequest( $method, $params, $id )
    {
        if ( empty( $id ) )
        {
            $id = md5( uniqid( null, true ) );
        }
        $request = array(
            'method' => $method,
            'params' => $params,
            'id' => $id,
            'jsonrpc' => '2.0'
        );
        return $request;
    }

    protected function _postQuery( $pQuery )
    {
        $ch = isset( $this->_curl ) ? $this->_curl : curl_init();
        $header = array();
        $options = array(
            CURLOPT_URL => $this->_serverUrl,
            CURLOPT_HEADER => 0,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $pQuery,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $this->_timeout,
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
        if ( empty( $this->_curl ) )
        {
            $this->_curl = $ch;
        }
        return $response_json;
    }

    protected function _postQueryDebug( $query )
    {
        $auth = '';
        if ( !empty( $this->_login ) )
        {
            $auth = sprintf( '--user %s:%s', $this->_login, $this->_password );
        }
        $command = sprintf( "curl -H 'Content-Type: application/json' -H 'Accept: application/json' -d '%s' %s %s | python -mjson.tool", $query, $auth, $this->_serverUrl );
        return $command;
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
        $v = $v && isset( $p[ 'jsonrpc' ] ) && $p[ 'jsonrpc' ] == '2.0';
        !isset( $p[ 'result' ] ) && $p[ 'result' ] = null;
        !isset( $p[ 'error' ] ) && $p[ 'error' ] = null;

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
}
