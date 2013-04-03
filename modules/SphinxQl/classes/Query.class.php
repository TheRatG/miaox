<?php
/**
 * Query.class.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 27.03.13 10:18
*/
require_once 'Query/Exception.class.php';
require_once 'Query/Select.class.php';

class Miaox_SphinxQl_Query
{
    protected $_backLink;

    protected $_queryString;

    static public function pivotArray( $array )
    {
        $result = array();

        foreach ( $array as $item )
        {
            if ( !is_array( $item ) )
            {
                $result[] = $item;
            }
            else
            {
                $result = array_merge( $result, self::pivotArray( $item ) );
            }
        }
        return $result;
    }

    public function __construct( Miaox_SphinxQl $backLink )
    {
        $this->_backLink = $backLink;
    }

    public function compile()
    {
        return $this->_backLink;
    }

    public function getCompiled()
    {
        return $this->_queryString;
    }

    public function setQueryString( $queryString )
    {
        $this->_queryString = $queryString;
    }
}
