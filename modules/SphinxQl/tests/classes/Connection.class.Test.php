<?php
/*
 * Don't forget run ./scripts/sphinx.sh start
 */
class Miaox_SphinxQl_Connection_Test extends PHPUnit_Framework_TestCase
{
	protected $_connection;

	public function setUp()
	{
		$this->_connection = new Miaox_SphinxQl_Connection( '127.0.0.1', 4499 );
	}

	public function testConnect()
	{
		$res = $this->_connection->connect();
		$this->assertTrue( $res );
	}

	public function testConnectException()
	{
		$this->setExpectedException( 'Miaox_SphinxQl_Connection_Exception' );

		$connection = new Miaox_SphinxQl_Connection( 'unknownhost', 7987 );
		$connection->connect();
	}

	public function testPing()
	{
		$this->_connection->connect();
		$condition = $this->_connection->ping();
		$this->assertTrue( $condition );
	}

	public function testClose()
	{
		$this->_connection->connect();
		$condition = $this->_connection->close();
		$this->assertTrue( $condition );
	}
}