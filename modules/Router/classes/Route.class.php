<?php
abstract class Miaox_Router_Route implements Miaox_Router_Route_Interface
{
    /**
     * @var string
     */
    protected $_view;

    /**
     * @var string
     */
    protected $_action;

    /**
     * @var Miaox_Router_Route_Param[]
     */
    protected $_params = array();

    /**
     * @param string $view
     */
    public function setView( $view )
    {
        $this->_view = $view;
    }

    /**
     * @param string $action
     */
    public function setAction( $action )
    {
        $this->_action = $action;
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * @return Miaox_Router_Route_Param[]
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * @return bool
     */
    public function isUriValid( $uri )
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isParamsValid( $paramValues )
    {
        return false;
    }


    /**
     * @param $paramValues
     *
     * @return string
     */
    public function genUrlByParams( $paramValues )
    {
        return '';
    }
}