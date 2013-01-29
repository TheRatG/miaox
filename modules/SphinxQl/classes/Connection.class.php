<?php
/**
 * @author vpak
 * @date 2013-01-22 09:45:41
 */
class Miaox_SphinxQl_Connection
{
	/**
	 * MySQLi
	 * @var MySQLi
	 */
	protected $_driver;

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
	protected $_multiQuery;

	public function __construct( $host, $port )
	{
		$this->_host = $host;
		$this->_port = $port;

		$this->_multiQuery = true;
		$config = Miao_Config::Libs( __CLASS__, false );
		if ( $config )
		{
			$this->_multiQuery = (bool) $config->get( 'multiquery', 1 );
		}
	}

	public function __destruct()
	{
		unset( $this->_driver );
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
			throw new Miaox_SphinxQl_Connection_Exception( $e->getMessage() );
		}
		unset( $exceptionizer );

		if ( $conn->connect_error )
		{
			throw new Miaox_SphinxQl_Connection_Exception( 'Connection error: [' . $conn->connect_errno . ']' . $conn->connect_error );
		}
		$this->_driver = $conn;
		return true;
	}

	/**
	 * Closes the connection to SphinxQL
	 */
	public function close()
	{
		$result = $this->_driver->close();
		return $result;
	}

	/**
	 * Ping the SphinxQL server
	 *
	 * @return  boolean  True if connected, false otherwise
	 */
	public function ping()
	{
		$result = $this->_driver->ping();
		return $result;
	}

	public function isConnected()
	{
		$result = !is_null( $this->_driver );
		return $result;
	}

	/**
	 * Sends the query to Sphinx
	 *
	 * @param  string  $query  The query string
	 * @return  array  The result array
	 * @throws  Miaox_SphinxQl_Connection_Exception  If the executed query produced an error
	 */
	public function query( $query )
	{
		if ( !$this->isConnected() )
		{
			$this->connect();
		}
		else
		{
			$this->ping();
		}

		$resource = $this->_driver->query( $query );

		if ( $this->_driver->error )
		{
			throw new Miaox_SphinxQl_Connection_Exception( '[' . $this->_driver->errno . '] ' . $this->_driver->error . ' [ ' . $query . ']' );
		}

		if ( $resource instanceof mysqli_result )
		{
			$rows = array();
			while ( !is_null( $row = $resource->fetch_assoc() ) )
			{
				$rows[] = $row;
			}
			$resource->free_result();
			$result = $rows;
		}
		else
		{
			// sphinxql doesn't return insert_id because we always have to point it out ourselves!
			$result = array(
				$this->_driver->affected_rows );
		}
		return $result;
	}

	public function multiQuery( $query )
	{
		if ( $this->_multiQuery )
		{
			$result = $this->_multiQuery( $query );
		}
		else
		{
			$result = $this->_emulateMultiQuery( $query );
		}
		return $result;
	}

	protected function _multiQuery( $query )
	{
		if ( !$this->isConnected() )
		{
			$this->connect();
		}
		else
		{
			$this->ping();
		}

		$this->_driver->multi_query( $query );
		if ( $this->_driver->error )
		{
			throw new Miaox_SphinxQl_Connection_Exception( '[' . $this->_driver->errno . '] ' . $this->_driver->error . ' [ ' . $query . ']' );
		}

		$result = array();
		$count = 0;
		do
		{
			if ( FALSE !== ( $resource = $this->_driver->store_result() ) )
			{
				$result[ $count ] = array();

				while ( !is_null( $row = $resource->fetch_assoc() ) )
				{
					$result[ $count ][] = $row;
				}

				$resource->free_result();
			}

			$count++;
		} while ( $this->_driver->next_result() );

		return $result;
	}

	protected function _emulateMultiQuery( $query )
	{
		$result = array();
		$list = explode( ';', $query );
		for( $i = 0, $cnt = count( $list ); $i < $cnt; $i++ )
		{
			$result[ $i ] = $this->query( $list[ $i ] );
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
	public function escape( $value )
	{
		if ( !$this->isConnected() )
		{
			$this->connect();
		}

		if ( ( $value = $this->_driver->real_escape_string( ( string ) $value ) ) === false )
		{
			throw new Miaox_SphinxQl_Connection_Exception( $this->_driver->error, $this->_driver->errno );
		}

		return "'" . $value . "'";
	}
}