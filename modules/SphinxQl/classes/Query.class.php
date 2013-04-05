<?php
/**
 * Query.class.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 27.03.13 10:18
 */
require_once 'Query/Exception.class.php';
require_once 'Query/Select.class.php';
require_once 'Query/Snippet.class.php';

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
                $result[ ] = $item;
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
        return $this->_queryString;
    }

    public function setQueryString( $queryString )
    {
        $this->_queryString = $queryString;
    }

    protected function _quote( $value )
    {
        $result = $value;
        if ( is_null( $value ) )
        {
            $result = 'null';
        }
        elseif ( is_numeric( $value ) )
        {
            $result = $value;
        }
        elseif ( is_float( $value ) )
        {
            // Convert to non-locale aware float to prevent possible commas
            $result = sprintf( '%F', $value );
        }
        else
        {
            $result = "'" . $this->_escape( $value ) . "'";
        }
        return $result;
    }

    protected function _escape( $string )
    {
        $from = array(
            '\\',
            '(',
            ')',
            '|',
            '-',
            '!',
            '@',
            '~',
            '"',
            '&',
            '/',
            '^',
            '$',
            '=',
            "'",
            "\x00",
            "\n",
            "\r",
            "\x1a"
        );
        $to = array(
            '\\\\',
            '\\\(',
            '\\\)',
            '\\\|',
            '\\\-',
            '\\\!',
            '\\\@',
            '\\\~',
            '\\\"',
            '\\\&',
            '\\\/',
            '\\\^',
            '\\\$',
            '\\\=',
            "\\'",
            "\\x00",
            "\\n",
            "\\r",
            "\\x1a"
        );
        $result = str_replace( $from, $to, $string );
        return $result;
    }
}
