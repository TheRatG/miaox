<?php
/**
 * @author vpak
 * @date 2013-01-22 09:45:15
 */
require_once 'Exception.class.php';
require_once 'Connection.class.php';
require_once 'Query.class.php';

class Miaox_SphinxQl
{
    const BETWEEN = 'BETWEEN';

    const IN = 'IN';

    const NOT_IN = 'NOT IN';

    /**
     * Order direction asc (1, 2, 3, 4...)
     */
    const ORDER_ASC = 'ASC';

    /**
     * Order direction desc (10, 9, 8, 7...)
     */
    const ORDER_DESC = 'DESC';

    const SHOW_META = 'SHOW META';

    const SHOW_WARNINGS = 'SHOW WARNINGS';

    const SHOW_STATUS = 'SHOW STATUS';

    const SHOW_TABLES = 'SHOW TABLES';

    const SHOW_VARIABLES = 'SHOW VARIABLES';

    const SHOW_SESSION_VARIABLES = 'SHOW SESSION VARIABLES';

    const SHOW_GLOBAL_VARIABLES = 'SHOW GLOBAL VARIABLES';

    /**
     * @var Miaox_SphinxQl_Connection
     */
    protected $_connection;

    /**
     * @var Miaox_SphinxQl_Query
     */
    protected $_query;

    protected $_queue = array();

    protected $_globalOptions = array();

    public function __construct( $host, $port, $noMultiQuery = false )
    {
        $this->_connection = new Miaox_SphinxQl_Connection( $host, $port, $noMultiQuery );
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws Miaox_SphinxQl_Exception
     */
    public function __call( $name, $arguments )
    {
        $result = null;
        if ( method_exists( $this->_query, $name ) )
        {
            $result = call_user_func_array( array( $this->_query, $name ), $arguments );
        }
        else
        {
            $message = sprintf( 'Method "%s" does not exists', $name );
            throw new Miaox_SphinxQl_Exception( $message );
        }
        return $result;
    }

    public function setGlobalOption( $option, $value )
    {
        assert( is_scalar( $option ) );
        assert( is_scalar( $option ) );
        $this->_globalOptions[ $option ] = $value;
    }

    public function select()
    {
        $this->_query = new Miaox_SphinxQl_Query_Select( $this );
        $attributes = Miaox_SphinxQl_Query::pivotArray( func_get_args() );
        $this->_query->setAttributes( $attributes );

        if ( !empty( $this->_globalOptions ) )
        {
            foreach ( $this->_globalOptions as $option => $value )
            {
                $this->option( $option, $value );
            }
        }

        return $this;
    }

    public function from()
    {
        $indexes = Miaox_SphinxQl_Query::pivotArray( func_get_args() );
        $this->_query->setIndexes( $indexes );
        return $this;
    }

    public function match( $column, $value = null, $escape = false )
    {
        if ( is_null( $value ) )
        {
            $value = $column;
            $column = '';
        }
        $this->_query->addMatchCondition( $column, $value, $escape );
        return $this;
    }

    public function where( $column, $operator, $value = null, $enclosingQuotes = true )
    {
        if ( is_null( $value ) )
        {
            $value = $operator;
            $operator = '=';
        }
        $this->_query->addWhereCondition( $column, $operator, $value, $enclosingQuotes );
        return $this;
    }

    public function orderBy( $column, $direction = Miaox_SphinxQl::ORDER_ASC )
    {
        $this->_query->addOrderBy( $column, $direction );
        return $this;
    }

    public function option( $option, $value )
    {
        $this->_query->setOption( $option, $value );
        return $this;
    }

    /**
     * Work like SQL limit: LIMIT [offset,] row_count
     * @param $offset
     * @param null $rowCount
     * @return $this
     */
    public function limit( $offset, $rowCount = null )
    {
        if ( is_null( $rowCount ) )
        {
            $this->_query->setRowCount( $offset );
        }
        else
        {
            $this->_query->setOffset( $offset );
            $this->_query->setRowCount( $rowCount );
        }
        return $this;
    }

    public function compile()
    {
        return $this->_query->compile();
    }

    public function execute( $query = null, &$meta = null )
    {
        if ( empty( $query ) )
        {
            $query = $this->compile();
        }
        if ( !is_null( $meta ) )
        {
            $this->enqueue();
            $this->enqueue( Miaox_SphinxQl::SHOW_META );
            $resultBatch = $this->executeBatch();
            if ( $resultBatch && isset( $resultBatch[ 0 ], $resultBatch[ 1 ] ) )
            {
                $result = $resultBatch[ 0 ];
                $meta = $this->processingResult( $resultBatch[ 1 ] );
            }
        }
        else
        {
            $result = $this->_connection->query( $query );
        }
        return $result;
    }

    public function enqueue( $query = null )
    {
        if ( empty( $query ) )
        {
            $query = $this->compile();
        }
        $this->_queue[ ] = $query;
    }

    public function executeBatch()
    {
        $query = implode( ';', $this->_queue );
        $result = $this->_connection->multiQuery( $query );
        return $result;
    }

    /**
     * Processing result from info query, for example "SHOW META"
     * @param array $list
     * @return array
     */
    public function processingResult( array $list )
    {
        $result = array();
        if ( is_array( $list ) && !empty( $list ) )
        {
            foreach ( $list as $item )
            {
                $index = current( $item );
                $value = next( $item );
                $result[ $index ] = $value;
            }
        }
        return $result;
    }
}
