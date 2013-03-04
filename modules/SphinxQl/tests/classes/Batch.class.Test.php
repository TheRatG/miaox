<?php
/*
 * Don't forget run ./scripts/sphinx.sh start
*/
class Miaox_SphinxQl_Batch_Test extends PHPUnit_Framework_TestCase
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

	public function testEnqueue()
	{
		$spinxql = $this->_sphinxql;
		$spinxql->select( 'id' )->from( 'articles' )->where( 'id', 1 );
		$spinxql->execute();

		$spinxql->select( 'id' )->from( 'articles' )->where( 'id', 2 )->enqueue();
		$spinxql->select( 'id' )->from( 'articles' )->where( 'id', 3 )->enqueue();
		$spinxql->executeBatch();
	}
}