<?php
class Miaox_Router_Route_Simple extends Miaox_Router_Route
{
    protected $_uriTemplate;

    function __construct($uriTemplate)
    {
        $this->_uriTemplate = $uriTemplate;
    }


    /**
     * @return bool
     */
    public function isUriValid( $uri )
    {
        return $this->_uriTemplate == $uri;
    }

    /**
     * @return bool
     */
    public function isParamsValid( $paramValues )
    {
        return true;
    }

    /**
     * @param array $paramValues
     *
     * @return string
     */
    public function genUrlByParams( $paramValues )
    {
        return $this->_uriTemplate;
    }
}
