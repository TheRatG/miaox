<?php
/**
 * Select.class.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 27.03.13 10:17
 */
class Miaox_SphinxQl_Query_Select extends Miaox_SphinxQl_Query
{
    private $_attributes = array();

    private $_indexes = array();

    private $_where = array();

    private $_match = array( array(), array() );

    private $_orderBy = array();

    public function setAttributes( array $attributes = array() )
    {
        $this->_attributes = $attributes;
    }

    public function setIndexes( $indexes )
    {
        $this->_indexes = $indexes;
    }

    public function addWhereCondition( $column, $operator, $value, $enclosingQuotes )
    {
        assert( is_scalar( $column ) );

        $item = array(
            'column' => $this->_prepareColumn( $column ),
            'operator' => $operator,
            'value' => $value,
            'enclosingQuotes' => $enclosingQuotes
        );

        //@TODO: check value if condition not multiply throw exception (for example IN is multi)
        $this->_where[ ] = $item;
    }

    public function addMatchCondition( $column, $value, $escape = false )
    {
        assert( is_scalar( $column ) );
        assert( is_scalar( $value ) );

        $item = array(
            'column' => $this->_prepareColumn( $column ),
            'value' => $value,
            'escape' => $escape
        );

        if ( !$column )
        {
            $this->_match[ 0 ][ ] = $item;
        }
        else
        {
            $this->_match[ 1 ][ ] = $item;
        }
    }

    public function addOrderBy( $column, $direction )
    {
        $item = array( 'column' => $column, 'direction' => $direction );
        $this->_orderBy[ ] = $item;
    }

    public function compile()
    {
        $queryString = array();
        $queryString[ ] = $this->_buildSelect();
        $queryString[ ] = $this->_buildFrom();
        $queryString[ ] = $this->_buildWhere();
        $queryString[ ] = $this->_buildOrderBy();
        $queryString = array_filter( $queryString );
        $this->setQueryString( implode( ' ', $queryString ) );
        return parent::compile();
    }

    protected function _buildSelect()
    {
        $result = array();
        $result[ ] = 'SELECT';
        $tmp = array();
        if ( $this->_attributes )
        {
            foreach ( $this->_attributes as $attribute )
            {
                $attribute = trim( $attribute );
                $needQuote = ( false === strpos( $attribute, '(' )
                    && false === stripos( $attribute, 'AS' )
                    && $attribute != '*'
                    && $attribute[ 0 ] !== '@' );
                if ( $needQuote )
                {
                    $tmp[ ] = sprintf( '`%s`', $attribute );
                }
                else
                {
                    $tmp[ ] = $attribute;
                }
            }
            $result[ ] = implode( ', ', $tmp );
        }
        else
        {
            $result[ ] = '*';
        }
        $result = implode( ' ', $result );
        return $result;
    }

    protected function _buildFrom()
    {
        if ( empty( $this->_indexes ) )
        {
            $message = 'Is missing the index definition. Use method Miaox_SphinxQl::from( $index ).';
            throw new Miaox_SphinxQl_Query_Exception( $message );
        }

        $result = array();
        $result[ ] = 'FROM';

        $tmp = array();
        foreach ( $this->_indexes as $index )
        {
            $tmp[ ] = sprintf( '`%s`', $index );
        }
        $result[ ] = implode( ', ', $tmp );
        $result = implode( ' ', $result );
        return $result;
    }

    protected function _buildWhere()
    {
        $result = array();

        if ( !empty( $this->_where ) || !$this->_isMatchEmpty() )
        {
            $result[ ] = 'WHERE';

            if ( !$this->_isMatchEmpty() )
            {
                $result[ ] = $this->_processMatch();
            }

            if ( !empty( $this->_where ) )
            {
                $result[ ] = $this->_processWhere();
            }
        }
        $result = implode( ' ', $result );
        return $result;
    }

    protected function _buildOrderBy()
    {
        $result = '';
        if ( !empty( $this->_orderBy ) )
        {
            $result = array();
            $result = implode( ', ', $result );
            $result = 'ORDER BY ' . $result;
        }
        return $result;
    }

    protected function _processWhere()
    {
        $conditionString = array();
        foreach ( $this->_where as $item )
        {
            if ( !isset( $item[ 'ext_operator' ] ) )
            {
                $column = $item[ 'column' ];
                $operator = strtoupper( $item[ 'operator' ] );
                $value = $item[ 'value' ];
                $enclosingQuotes = $item[ 'enclosingQuotes' ];

                $tmp = array();
                if ( !$enclosingQuotes || 'id' == $column || '@' == $column[ 0 ] )
                {
                    $tmp[ ] = $column;
                }
                else
                {
                    $tmp[ ] = '`' . $column . '`';
                }
                $tmp[ ] = $operator;
                switch ( $operator )
                {
                    case Miaox_SphinxQl::BETWEEN:
                        $tmp[ ] = $value[ 0 ];
                        $tmp[ ] = 'AND';
                        $tmp[ ] = $value[ 1 ];
                        break;
                    case Miaox_SphinxQl::IN:
                    case Miaox_SphinxQl::NOT_IN:
                        $tmp[ ] = '(';
                        $tmp[ ] = implode( ', ', $value );
                        $tmp[ ] = ')';
                        break;
                    default:
                        if ( !is_array( $value ) )
                        {
                            $tmp[ ] = $value;
                        }
                }
                $conditionString[ ] = implode( ' ', $tmp );
            }
        }
        $result = implode( ' AND ', $conditionString );
        return $result;
    }

    protected function _processMatch()
    {
        $conditionString = array();
        foreach ( $this->_match[ 0 ] as $item )
        {
            $value = $item[ 'value' ];
            $escape = $item[ 'escape' ];

            if ( $escape )
            {
                $value = $this->_escape( $value );
            }
            $conditionString[ ] = $value;
        }
        foreach ( $this->_match[ 1 ] as $item )
        {
            $column = $item[ 'column' ];
            $value = $item[ 'value' ];
            $escape = $item[ 'escape' ];

            if ( $escape )
            {
                $value = $this->_escape( $value );
            }
            $conditionString[ ] = sprintf( '@%s %s', $column, $value );
        }

        $result = implode( ' ', $conditionString );
        $result = sprintf( "MATCH ('%s')", $result );
        return $result;
    }

    protected function _isMatchEmpty()
    {
        $result = true;
        if ( count( $this->_match[ 0 ] ) || count( $this->_match[ 1 ] ) )
        {
            $result = false;
        }
        return $result;
    }

    protected function _prepareColumn( $column )
    {
        $result = ltrim( $column, '@' );
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
