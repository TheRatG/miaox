<?php
/**
 * @author vpak
 * @date 2013-01-22 09:45:41
 */
class Miaox_Search_SphinxQl_Connection
{
	/**
	 * MySQLi connection
	 * @var MySQLi
	 */
	protected $_connection;

	/**
	 *
	 * @var string Host
	 */
	protected $_host;
	/**
	 *
	 * @var string Port
	 */
	protected $_port;

	public function __construct( $host, $port )
	{
		$this->_host = $host;
		$this->_port = $port;
	}

	/**
	 * Establishes connection to SphinxQL with MySQLi
	 * @param unknown_type $suppressError
	 */
	public function connect()
	{
		$exceptionizer = new Miaox_Exceptionizer( E_ALL );
		try
		{
			$conn = new MySQLi( $this->_host, null, null, null, $this->_port, null );
		}
		catch ( Miaox_Exceptionizer_Exception $e )
		{
			throw new Miaox_Search_SphinxQl_Connection_Exception( $e->getMessage() );
		}
		unset( $exceptionizer );

		if ( $conn->connect_error )
		{
			throw new Miaox_Search_SphinxQl_Connection_Exception( 'Connection error: [' . $conn->connect_errno . ']' . $conn->connect_error );
		}
		$this->_connection = $conn;
		return true;
	}

	/**
	 * Closes the connection to SphinxQL
	 */
	public function close()
	{
		$result = $this->_connection->close();
		return $result;
	}

	/**
	 * Ping the SphinxQL server
	 *
	 * @return  boolean  True if connected, false otherwise
	 */
	public function ping()
	{
		$result = $this->_connection->ping();
		return $result;
	}

	/**
	 * Sends the query to Sphinx
	 * @param string $query The query string
	 * @return  array  The result array
	 */
// 	public function query( $query )
// 	{
// 		if ( !$this->ping() )
// 		{
// 			$this->connect();
// 		}
// 		$resource = $this->_connection->query( $query );
// 	}
}