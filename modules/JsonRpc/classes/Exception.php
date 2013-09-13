<?php
/**
 * @author Alexander Rodionov <avrodionov@rbc.ru>
 * $Id$
 */

namespace Miaox\JsonRpc;


class Exception extends \Exception
{
    /**
     * ответ сервера
     * @var string
     */
    protected $_response;

    /**
     * комманда для дебага
     * @var string
     */
    protected $_debugCommand;

    /**
     * @param string $debugCommand
     * @return self
     */
    public function setDebugCommand( $debugCommand )
    {
        $this->_debugCommand = $debugCommand;
        return $this;
    }

    /**
     * @return string
     */
    public function getDebugCommand()
    {
        return $this->_debugCommand;
    }

    /**
     * @param string $response
     * @return self
     */
    public function setResponse( $response )
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->_response;
    }

}