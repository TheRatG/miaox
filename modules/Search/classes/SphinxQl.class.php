<?php
/**
 * @author vpak
 * @date 2013-01-22 09:45:15
 */
class Miaox_Search_SphinxQl extends Miaox_Search_SphinxQl_Query
{
	protected $_connection;

	public function __construct( $host, $port )
	{
		$this->_connection = new Miaox_Search_SphinxQl_Connection( $host, $port );
	}

	public function __destruct()
	{
		unset( $this->_connection );
	}

	public function execute()
	{
		$str = $this->compile()->getCompiled();
		$result = $this->_query( $str );
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
	public function escape( $value )
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