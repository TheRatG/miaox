<?php
class Miaox_Router_Route_Standart extends Miaox_Router_Route
{
    protected $_uriTemplate;
    protected $_view;
    protected $_action;


    /**
     * @param string $urlTemplate
     */
    public function __construct( $uriTemplate )
    {
        $this->_uriTemplate = $uriTemplate;
        $this->_parseParams();
    }

    public function addValidator( $paramName, Miaox_Router_Route_Validator_Interface $validator )
    {
        if ( !empty ( $this->_params[$paramName] ) )
        {
            $this->_params[$paramName]->addValidator( $validator );
        }
    }

    public function isUriValid( $uri )
    {
        $templateParts = $this->_getParts( $this->_uriTemplate );
        $urlParts = $this->_getParts( $uri );

        $ret = true;

        $paramValues = array();

        foreach ( $this->_params as $param )
        {
            $paramValues[$param->getName()] = null;
        }

        for ( $i = 0; $i < count( $urlParts ); $i++ )
        {
            if ( false !== ( $paramName = $this->_getParamName( $templateParts[$i] ) ) )
            {
                $paramValues[$paramName] = $urlParts[$i];
            }
            elseif ( $urlParts[$i] != $templateParts[$i] )
            {
                $ret = false;
                break;
            }
        }

        if ( $ret )
        {
            if ( $ret = $this->isParamsValid( $paramValues ) )
            {
                $this->_setParamValues( $paramValues );
            }
        }
        return $ret;
    }

    /**
     * @param array $paramValues
     *
     * @return bool
     */
    public function isParamsValid( $paramValues )
    {
        $ret = true;

        $paramValues = $this->_fillEmptyParams( $paramValues );

        foreach ( $paramValues as $paramName => $value )
        {
            if ( !$this->_params[$paramName]->isValid( $value ) )
            {
                $ret = false;
                break;
            }
        }
        return $ret;
    }

    protected function _fillEmptyParams( $paramValues, $defValue = null )
    {
        foreach ( $this->_params as $param )
        {
            if ( !isset( $paramValues[$param->getName()] ) )
            {
                $paramValues[$param->getName()] = $defValue;
            }
        }
        return $paramValues;
    }

    protected function _setParamValues( $paramValues )
    {
        foreach ( $paramValues as $paramName => $value )
        {
            $this->_params[$paramName]->setValue( $value );
        }
    }

    /**
     * @param $paramValues
     *
     * @return string
     */
    public function genUrlByParams( $paramValues )
    {
        $ret = $this->_uriTemplate;
        $paramValues = $this->_fillEmptyParams( $paramValues, '' );
        foreach ( $paramValues as $name => $value )
        {
            $ret = str_replace( '/:' . $name, '/' . $value, $ret );
        }

        return $ret;
    }

    protected function _parseParams()
    {
        $uri = $this->_uriTemplate;
        $parts = $this->_getParts( $uri );
        foreach ( $parts as $part )
        {
            if ( false !== ( $name = $this->_getParamName( $part ) ) )
            {
                $param = new Miaox_Router_Route_Param( $name );
                $this->_params[$name] = $param;
            }
        }
    }

    protected function _getParts( $uri )
    {
        $parts = explode( '/', $uri );
        $ret = array();
        foreach ( $parts as $part )
        {
            $part = trim( $part );
            if ( empty ( $part ) )
            {
                continue;
            }
            $ret[] = $part;
        }
        return $ret;
    }

    /**
     * @param string $part
     *
     * @return bool|string
     */
    protected function _getParamName( $part )
    {
        if ( substr( $part, 0, 1 ) == ':' )
        {
            return substr( $part, 1 );
        }
        return false;
    }
}
