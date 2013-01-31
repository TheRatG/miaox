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
	public function executeBatch()
	{
		$queue = $this->_queue;
		if ( count( $queue ) === 0 )
		{
			throw new Miaox_SphinxQl_Connection_Exception( 'The Queue is empty.' );
		}
		$result = $this->_multiQuery( $queue );
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
			$query = implode( ';', $queue );
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