<?php
/**
 * Select.class.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 27.03.13 10:17
 */
class Miaox_SphinxQl_Query_Select extends Miaox_SphinxQl_Query
{
    /**
     *  any of 'proximity_bm25', 'bm25', 'none', 'wordcount', 'proximity', 'matchany', or 'fieldmask'
     */
    const OPTION_RANKER = 'ranker';

    /**
     * 'max_matches' - integer (per-query max matches value)
     */
    const OPTION_MAX_MATCHES = 'max_matches';

    /**
     * 'cutoff' - integer (max found matches threshold)
     */
    const OPTION_CUTOFF = 'cutoff';

    /**
     * 'max_query_time' - integer (max search time threshold, msec)
     */
    const OPTION_MAX_QUERY_TIME = 'max_query_time';

    /**
     * 'retry_count' - integer (distributed retries count)
     */
    const OPTION_RETRY_COUNT = 'retry_count';

    /**
     * 'retry_delay' - integer (distributed retry delay, msec)
     */
    const OPTION_RETRY_DELAY = 'retry_delay';

    /**
     * 'field_weights' - a named integer list (per-field user weights for ranking)
     */
    const OPTION_FIELD_WEIGHTS = 'field_weights';

    /**
     * 'index_weights' - a named integer list (per-index user weights for ranking)
     */
    const OPTION_INDEX_WEIGHTS = 'index_weights';

    /**
     * 'reverse_scan' - 0 or 1, lets you control the order in which full-scan query processes the rows
     */
    const OPTION_REVERSE_SCAN = 'reverse_scan';

    /**
     * 'comment' - string, user comment that gets copied to a query log file
     */
    const OPTION_COMMENT = 'comment';

    protected $_options = array();

    protected $_optionsMap = array(
        self::OPTION_RANKER,
        self::OPTION_MAX_MATCHES,
        self::OPTION_CUTOFF,
        self::OPTION_MAX_QUERY_TIME,
        self::OPTION_RETRY_COUNT,
        self::OPTION_RETRY_DELAY,
        self::OPTION_FIELD_WEIGHTS,
        self::OPTION_INDEX_WEIGHTS,
        self::OPTION_REVERSE_SCAN,
        self::OPTION_COMMENT
    );

    private $_attributes = array();

    private $_indexes = array();

    private $_where = array();

    private $_match = array( array(), array() );

    private $_groupBy = array();

    private $_orderBy = array();

    private $_offset = null;

    private $_rowCount = null;

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

        if ( in_array(
            $operator, array( Miaox_SphinxQl::IN, Miaox_SphinxQl::NOT_IN, Miaox_SphinxQl::BETWEEN )
        )
            && !is_array( $value )
        )
        {
            $msg = sprintf( 'Invalid param $value, must be array, because $operator is "IN"', $operator );
            throw new Miaox_SphinxQl_Query_Exception( $msg );
        }
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

    public function addGroupBy( $column )
    {
        $this->_groupBy[ ] = $column;
    }

    public function addOrderBy( $column, $direction )
    {
        $item = array( 'column' => $column, 'direction' => $direction );
        $this->_orderBy[ ] = $item;
    }

    public function setRowCount( $rowCount )
    {
        assert( is_numeric( $rowCount ) );
        $this->_rowCount = $rowCount;
    }

    public function setOffset( $offset )
    {
        assert( is_numeric( $offset ) );
        $this->_offset = $offset;
    }

    public function setOption( $option, $value )
    {
        if ( !in_array( $option, $this->_optionsMap ) )
        {
            $msg = sprintf( 'Invalid option name (%s)', $option );
            throw new Miaox_SphinxQl_Query_Exception( $msg );
        }
        $this->_options[ $option ] = $value;
    }

    public function compile()
    {
        $queryString = array();
        $queryString[ ] = $this->_buildSelect();
        $queryString[ ] = $this->_buildFrom();
        $queryString[ ] = $this->_buildWhere();
        $queryString[ ] = $this->_buildGroupBy();
        $queryString[ ] = $this->_buildOrderBy();
        $queryString[ ] = $this->_buildLimit();
        $queryString[ ] = $this->_buildOption();
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
                $needQuote = $this->_isNeedQuote( $attribute );
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
                if ( !$this->_isMatchEmpty() )
                {
                    $result[ ] = 'AND';
                }

                $result[ ] = $this->_processWhere();
            }
        }
        $result = implode( ' ', $result );
        return $result;
    }

    protected function _buildGroupBy()
    {
        $result = array();
        if ( !empty( $this->_groupBy ) )
        {
            $result[ ] = 'GROUP BY';
            $result[ ] = implode( ', ', $this->_groupBy );
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

            foreach ( $this->_orderBy as $item )
            {
                $column = $item[ 'column' ];
                $direction = $item[ 'direction' ];

                $needQuote = $this->_isNeedQuote( $column );
                if ( $needQuote )
                {
                    $result[ ] = sprintf( '`%s` %s', $column, $direction );
                }
                else
                {
                    $result[ ] = sprintf( '%s %s', $column, $direction );
                }                
            }
            $result = implode( ', ', $result );
            $result = 'ORDER BY ' . $result;
        }
        return $result;
    }

    protected function _buildLimit()
    {
        $offset = $this->_offset;
        $limit = $this->_rowCount;
        $result = '';
        if ( !is_null( $offset ) || !is_null( $limit ) )
        {
            $result = array();
            $result[ ] = 'LIMIT';
            if ( ( !is_null( $offset ) && !is_null( $limit ) ) || !is_null( $offset ) )
            {
                if ( is_null( $limit ) )
                {
                    $limit = isset( $this->_options[ self::OPTION_MAX_MATCHES ] ) ? $this->_options[ self::OPTION_MAX_MATCHES ] : PHP_INT_MAX;
                }
                $result[ ] = sprintf( "%s, %s", $offset, $limit );
            }
            else if ( $limit )
            {
                $result[ ] = $limit;
            }
            $result = implode( ' ', $result );
        }
        return $result;
    }

    protected function _buildOption()
    {
        $result = '';
        if ( !empty( $this->_options ) )
        {
            $result = array();
            foreach ( $this->_options as $option => $value )
            {
                $result[ ] = sprintf( '%s=%s', $option, $value );
            }
            $result = 'OPTION ' . implode( ', ', $result );
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

    protected function _isNeedQuote( $attribute )
    {
        $attribute = trim( $attribute );
        $result = ( false === strpos( $attribute, '(' )
                    && false === stripos( $attribute, 'AS' )
                    && $attribute[ 0 ] !== '@'
                    && !in_array( $attribute, array( '*', 'id', 'weight' ) )
        );
        return $result;
    }

}
