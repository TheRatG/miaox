<?php
require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_Connection_Test extends Miaox_SphinxQl_Helper_Test
{
    /**
     * @var Miaox_SphinxQl_Connection
     */
    protected $_connection;

    public function setUp()
    {
        $this->_connection = new Miaox_SphinxQl_Connection( SEARCHD_HOST, SEARCHD_PORT );
    }

	public function testConnect()
	{
		$res = $this->_connection->connect();
		$this->assertTrue( $res );
	}

	public function testConnectException()
	{
		$this->setExpectedException( 'Miaox_SphinxQl_Connection_Exception' );

		$connection = new Miaox_SphinxQl_Connection( 'unknown_host', 7987 );
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
