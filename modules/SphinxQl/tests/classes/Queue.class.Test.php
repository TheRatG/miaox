<?php
/*
 * Don't forget run ./scripts/sphinx.sh start
*/
class Miaox_SphinxQl_Queue_Test extends PHPUnit_Framework_TestCase
{
	protected $_sphinxql;

	public function setUp()
	{
		$this->_sphinxql = new Miaox_SphinxQl( '127.0.0.1', 4499 );
	}

	public function tearDown()
	{
		unset( $this->_sphinxql );
	}

	public function testExecute()
	{
		$this->_sphinxql->select( 'group' )->from( 'articles' )->where( 'id', 1 )->enqueue();
		$this->_sphinxql->select( 'group' )->from( 'articles' )->where( 'id', 2 )->enqueue();

		$actual = $this->_sphinxql->executeBatch();
		$expected = array(
			0 => array(
				0 => array(
					'group' => '45' ) ),
			1 => array(
				0 => array(
					'group' => '46' ) ) );

		$this->assertEquals( $expected, $actual );
	}
}