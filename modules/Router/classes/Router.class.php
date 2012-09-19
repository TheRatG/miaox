<?php
class Miaox_Router
{
    /**
     * @var Miaox_Router
     */
    static protected $_instance;

    /**
     * @var Miaox_Router_Route_Interface[]
     */
    protected $_routes = array();

    /**
     * @var Miaox_Router_Route_Interface[]
     */
    protected $_errorRoutes = array();

    /**
     * @var Miaox_Router_Route_Interface
     */
    protected $_currentRoute = null;

    protected function __construct()
    {

    }

    protected function __clone()
    {

    }

    static function getInstance()
    {
        if ( !( self::$_instance instanceof self ) )
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param array $conf
     *
     * @return Miaox_Router
     */
    public function loadRoutesFromArray( array $conf )
    {
        if ( empty ( $conf['route'] ) )
        {
            throw new Miaox_Router_Exception( "Routes have not been found" );
        }

        if ( empty ( $conf['route'][0] ) )
        {
            $conf['route'] = array( $conf['route'] );
        }

        $this->_initRoutes( $conf['route'] );
        if ( !empty ( $conf['error'] ) )
        {
            if ( empty ( $conf['error'][0] ) )
            {
                $conf['error'] = array( $conf['error'] );
            }
            $this->_initErrorRoutes( $conf['error'] );
        }
        return $this;
    }

    protected function _initRoutes( $conf )
    {
        foreach ( $conf as $row )
        {
            $row = array_change_key_case( $row );

            if ( empty( $row['url'] ) )
            {
                continue;
            }

            $route = $this->_makeRoute( $row['url'] );
            $this->_initRouteValues( $row, $route );
            $this->_initRouteValidators( $row, $route );
            $this->addRoute( $route );
        }
    }

    protected function _initErrorRoutes( $conf )
    {
        foreach ( $conf as $row )
        {
            if ( empty ( $row['code'] ) )
            {
                continue;
            }
            $route = new Miaox_Router_Route_Error( $row['code'] );
            $this->_initRouteValues( $row, $route );
            $this->addErrorRoute( $route, $row['code'] );
        }
    }

    protected function _initRouteValues( $row, Miaox_Router_Route_Interface $route )
    {
        if ( !empty( $row['view'] ) )
        {
            $route->setView( $row['view'] );
            return $row;
        }
        elseif ( !empty ( $row['action'] ) )
        {
            $route->setAction( $row['action'] );
            return $row;
        }
        return $row;
    }

    protected function _initRouteValidators( $row, $route )
    {
        if ( !empty ( $row['validator'] ) )
        {

            if ( empty ( $row['validator'][0] ) )
            {
                $row['validator'] = array( $row['validator'] );
            }

            foreach ( $row['validator'] as $validatorDef )
            {
                $validatorDef = array_change_key_case( $validatorDef );
                if ( empty ( $validatorDef['param'] ) )
                {
                    continue;
                }
                $validator = $this->_makeValidator( $validatorDef );
                $route->addValidator( $validatorDef['param'], $validator );
            }
        }
    }

    /**
     * Removes all routes, set current route = null
     */
    public function clearRoutes()
    {
        $this->_routes = array();
        $this->_errorRoutes = array();
        $this->_currentRoute = null;
    }

    /**
     * @param Miaox_Router_Route_Interface $route
     */
    public function addRoute( Miaox_Router_Route_Interface $route )
    {
        $this->_routes[] = $route;
    }

    /**
     * @param integer $code
     * @param Miaox_Router_Route_Interface $route
     */
    public function addErrorRoute( Miaox_Router_Route_Interface $route, $code )
    {
        $this->_errorRoutes[$code] = $route;
    }

    /**
     * Returns all inserted routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     * @param string $requestUri
     *
     * @return Miaox_Router_Route_Interface
     * @throws Miaox_Router_Exception
     */
    public function getRoute( $requestUri = null )
    {

        if ( $this->_currentRoute instanceof Miaox_Router_Route_Interface )
        {
            return $this->_currentRoute;
        }

        if ( is_null( $requestUri ) )
        {
            if ( !empty ( $_SERVER['REQUEST_URI'] ) )
            {
                list( $requestUri ) = explode( '?', $_SERVER['REQUEST_URI'] );
            }
            else
            {
                throw new Miaox_Router_Exception( 'Param \'$requestUri\' is undefined' );
            }
        }

        foreach ( $this->_routes as $route )
        {
            if ( $route->isUriValid( $requestUri ) )
            {
                $this->_currentRoute = $route;
            }
        }

        if ( empty ( $this->_currentRoute ) )
        {
            throw new Miaox_Router_Exception_RouteNotFound( "Route for '$requestUri' has not been found" );
        }

        return $this->_currentRoute;
    }

    public function getErrorRoute( $code )
    {
        if ( !isset( $this->_errorRoutes[$code] ) )
        {
            throw new Miaox_Router_Exception_RouteNotFound( "Route for $code error code has not been found" );
        }
        return $this->_errorRoutes[$code];
    }

    /**
     * If $name is null, it returns array of params, else it returns param value
     *
     * @param $name
     *
     * @return mixed
     */
    public function getParam( $name = null )
    {
        $params = $this->_currentRoute->getParams();
        $ret = array();
        foreach ( $params as $param )
        {
            $ret[$param->getName()] = $param->getValue();
        }
        if ( !is_null( $name ) )
        {
            $ret = isset( $ret[$name] ) ? $ret[$name] : null;
        }
        return $ret;
    }

    /**
     * @param string $view
     * @param array $params
     *
     * @return string
     */
    public function genUrlByView( $view, $params = array() )
    {
        return $this->_genUrlByViewAndAction( $view, null, $params );
    }

    /**
     * @param string $action
     * @param array $params
     *
     * @return string
     */
    public function genUrlByAction( $action, $params = array() )
    {
        return $this->_genUrlByViewAndAction( null, $action, $params );
    }

    protected function _genUrlByViewAndAction( $view = null, $action = null, $paramValues = array() )
    {
        $ret = '';
        foreach ( $this->_routes as $route )
        {
            if ( !is_null( $view ) )
            {
                if ( $view == $route->getView() && $route->isParamsValid( $paramValues ) )
                {
                    $ret = $route->genUrlByParams( $paramValues );
                    break;
                }
            }
            elseif ( !is_null( $action ) )
            {
                if ( $action == $route->getAction() && $route->isParamsValid( $paramValues ) )
                {
                    $ret = $route->genUrlByParams( $paramValues );
                    break;
                }
            }
        }
        return $ret;
    }

    /**
     * @param array $validatorDef
     *
     * @return Miaox_Router_Route_Validator_Interface
     */
    protected function _makeValidator( $validatorDef )
    {
        $validatorDef['type'] = empty ( $validatorDef['type'] ) ? 'NotEmpty' : $validatorDef['type'];
        switch ( strtolower( $validatorDef['type'] ) )
        {
            case 'regexp':
                $validatorDef['regexp'] = empty ( $validatorDef['regexp'] ) ? '.+' : $validatorDef['regexp'];
                $ret = new Miaox_Router_Route_Validator_Regexp( $validatorDef['regexp'] );
                break;
            case 'numeric':
                $ret = new Miaox_Router_Route_Validator_Numeric();
                break;
            case 'notempty':
            default:
                $ret = new Miaox_Router_Route_Validator_NotEmpty();
                break;
        }
        return $ret;
    }


    /**
     * @param $url
     *
     * @return Miaox_Router_Route_Interface
     */
    protected function _makeRoute( $url )
    {
        if ( $this->_hasVariables( $url ) )
        {
            $route = new Miaox_Router_Route_Standart( $url );
        }
        else
        {
            $route = new Miaox_Router_Route_Simple( $url );
        }
        return $route;
    }

    /**
     * @param string $uri
     *
     * @return bool
     */
    protected function _hasVariables( $uri )
    {
        return strpos( $uri, '/:' ) !== false;
    }


}
