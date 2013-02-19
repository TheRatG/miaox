<?php
/**
 * @author vpak
 * @date 2013-01-22 09:45:15
 */
class Miaox_SphinxQl extends Miaox_SphinxQl_Query
{
	const ORDER_ASC = 'asc';
	const ORDER_DESC = 'desc';

	/**
	 * Ready for use queries
	 *
	 * @var  array
	 */
	protected static $_showQueries = array(
		'meta' => 'SHOW META',
		'warnings' => 'SHOW WARNINGS',
		'status' => 'SHOW STATUS',
		'tables' => 'SHOW TABLES',
		'variables' => 'SHOW VARIABLES',
		'variablesSession' => 'SHOW SESSION VARIABLES',
		'variablesGlobal' => 'SHOW GLOBAL VARIABLES' );

	/**
	 *
	 * @var Miao_Log
	 */
	protected $_log;

	/**
	 *
	 * @var Miaox_SphinxQl_Connection
	 */
	protected $_connection;

	/**
	 *
	 * @var array
	 */
	protected $_queue = array();

	public function getLog()
	{
		if ( is_null( $this->_log ) )
		{
			$this->_log = Miao_Log::easyFactory();
		}
		return $this->_log;
	}

	public function __construct( $host, $port, Miao_Log $log = null )
	{
		$this->_log = $log;
		$msg = sprintf( 'Try to connect: host - %s, port - %s', $host, $port );
		$this->getLog()->debug( $msg );

		$this->_connection = new Miaox_SphinxQl_Connection( $host, $port );
		$this->getLog()->debug( 'Connected' );
	}

	public function __destruct()
	{
		unset( $this->_connection );
	}

	public function __call( $method, $args )
	{
		$result = array();
		if ( array_key_exists( $method, self::$_showQueries ) )
		{
			$str = self::$_showQueries[ $method ];
			$list = $this->_query( $str );

			if ( is_array( $list ) )
			{
				$result = $this->processingResult( $list );
			}
		}
		else
		{
			$msg = sprintf( 'Method %s does not exists', $method );
			throw new Miaox_SphinxQl_Exception( $msg );
		}
		return $result;
	}

	/**
	 * Execute last select query
	 * @return Ambigous <multitype:, multitype:NULL , multitype:multitype: >
	 */
	public function execute()
	{
		$str = $this->compile()->getCompiled();
		$result = $this->_query( $str );
		return $result;
	}

	/**
	 * Added query to queue
	 * @param unknown_type $query
	 * @return Miaox_SphinxQl
	 */
	public function enqueue( $query = '' )
	{
		if ( empty( $query ) )
		{
			$query = $this->compile()->getCompiled();
		}
		$this->_queue[] = $query;
		return $this;
	}

	/**
	 * Execute multi query
	 * @throws Miaox_SphinxQl_Connection_Exception
	 * @return Ambigous <multitype:, multitype:multitype: >
	 */
	public function executeBatch( $clearQueue = true )
	{
		$queue = $this->_queue;
		if ( count( $queue ) === 0 )
		{
			throw new Miaox_SphinxQl_Connection_Exception( 'The Queue is empty.' );
		}
		$result = $this->_multiQuery( $queue );
		if ( $result && $clearQueue )
		{
			$this->_queue = array();
		}
		return $result;
	}

	/**
	 * Proccessing result from info query, for example "SHOW META"
	 * @param array $list
	 * @return multitype:mixed
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

	/**
	 * CALL SNIPPETS syntax
	 *
	 * @example $opts
	 * <code>
	 * $opts = array(
	 *	"before_match" => '<span class="find-text">',
	 *	"after_match" => "</span>",
	 *	"chunk_separator" => " ... ",
	 *	"limit" => 200,
	 *	"around" => 10 );
	 * </code>
	 *
	 * @param string $data
	 * @param string $index
	 * @param array $extra
	 *
	 * @return array The result of the query
	 */
	public function callSnippets( $docs, $index, $query, $opts = array() )
	{
		$buildQuery = $this->_compileCallSnippets( $docs, $index, $query, $opts );
		$queryResult = $this->_query( $buildQuery );
		$result = array();

		if ( !empty( $queryResult ) )
		{
			$i = 0;
			if ( is_array( $docs ) )
			{
				foreach ( array_keys( $docs ) as $key )
				{
					$result[ $key ] = stripcslashes( current( $queryResult[ $i++ ] ) );
				}
			}
			else
			{
				$result = stripcslashes( current( $queryResult[ $i ] ) );
			}
		}
		return $result;
	}

	/**
	 * CALL KEYWORDS syntax
	 *
	 * @param string $text
	 * @param string $index
	 * @param null|string $hits
	 *
	 * @return array The result of the query
	 */
	public function callKeywords( $text, $index, $hits = null )
	{
		$arr = array(
			$text,
			$index );
		if ( $hits !== null )
		{
			$arr[] = $hits;
		}

		return $this->getConnection()->query( 'CALL KEYWORDS(' . implode( ', ', $this->getConnection()->quoteArr( $arr ) ) . ')' );
	}

	/**
	 * DESCRIBE syntax
	 *
	 * @param string $index The name of the index
	 *
	 * @return array The result of the query
	 */
	public function describe( $index )
	{
		$query = 'DESCRIBE ' . $this->_quoteIdentifier( $index );
		$result = $this->_query( $query );
		return $result;
	}

	/**
	 * SET syntax
	 *
	 * @param string $name The name of the variable
	 * @param mixed $value The value o the variable
	 * @param boolean $global True if the variable should be global, false otherwise
	 *
	 * @return array The result of the query
	 */
	public function setVariable( $name, $value, $global = false )
	{
		$query = 'SET ';

		if ( $global )
		{
			$query .= 'GLOBAL ';
		}

		$user_var = strpos( $name, '@' ) === 0;

		// if it has an @ it's a user variable and we can't wrap it
		if ( $user_var )
		{
			$query .= $name . ' ';
		}
		else
		{
			$query .= $this->_quoteIdentifier( $name ) . ' ';
		}

		// user variables must always be processed as arrays
		if ( $user_var && !is_array( $value ) )
		{
			$query .= '= (' . $this->_quote( $value ) . ')';
		}
		elseif ( is_array( $value ) )
		{
			$query .= '= (' . implode( ', ', $this->_quoteArr( $value ) ) . ')';
		}
		else
		{
			$query .= '= ' . $this->_quote( $value );
		}

		$this->_query( $query );
	}

	/**
	 * Escapes the input with real_escape_string
	 * Taken from FuelPHP and edited
	 *
	 * @param  string  $value  The string to escape
	 *
	 * @return  string  The escaped string
	 * @throws  Miaox_SphinxQl_Connection_Exception  If there was an error during the escaping
	 */
	protected function _escape( $value )
	{
		$result = $this->_connection->escape( $value );
		return $result;
	}

	/**
	 * @param string $query string
	 * @return array
	 */
	protected function _query( $query )
	{
		$result = null;
		try
		{
			$this->getLog()->debug( $query );
			$result = $this->_connection->query( $query );
			$msg = sprintf( 'Result cnt: %d', count( $result ) );
			$this->getLog()->debug( $msg );
		}
		catch ( Miaox_SphinxQl_Exception $e )
		{
			$this->getLog()->err( $e->getMessage() );
			throw $e;
		}
		return $result;
	}

	/**
	 * @param string $query string
	 * @return array
	 */
	protected function _multiQuery( array $queue )
	{
		$result = null;
		try
		{
			$query = implode( '; ', $queue ) . ';';
			$this->getLog()->debug( $query );
			$result = $this->_connection->multiQuery( $query );
			$msg = sprintf( 'Result cnt: %d', count( $result ) );
			$this->getLog()->debug( $msg );
		}
		catch ( Miaox_SphinxQl_Exception $e )
		{
			$this->getLog()->err( $e->getMessage() );
			throw $e;
		}
		return $result;
	}
}