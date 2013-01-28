<?php
/**
 * @author vpak
 * @date 2013-01-22 09:45:15
 */
class Miaox_Search_SphinxQl extends Miaox_Search_SphinxQl_Query
{
	const ORDER_ASC = 'asc';
	const ORDER_DESC = 'desc';

	/**
	 *
	 * @var Miao_Log
	 */
	protected $_log;

	protected $_connection;

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

		$this->_connection = new Miaox_Search_SphinxQl_Connection( $host, $port );
		$this->getLog()->debug('Connected');
	}

	public function __destruct()
	{
		unset( $this->_connection );
	}

	public function execute()
	{
		try
		{
			$str = $this->compile()->getCompiled();
			$this->getLog()->debug( $str );
			$result = $this->_query( $str );

			$msg = sprintf( 'Result cnt: %d', count( $result ) );
			$this->getLog()->debug( $msg );
		}
		catch ( Miaox_Search_SphinxQl_Exception $e )
		{
			$this->getLog()->err( $e->getMessage() );
			throw $e;
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
	 * @throws  Miaox_Search_SphinxQl_Connection_Exception  If there was an error during the escaping
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
		$result = $this->_connection->query( $query );
		return $result;
	}
}