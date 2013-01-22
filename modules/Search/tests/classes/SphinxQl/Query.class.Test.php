<?php
/*
 * Don't forget run ./scripts/sphinx.sh start
*/
class Miaox_Search_SphinxQl_Query_Test extends PHPUnit_Framework_TestCase
{

	public function testSelect()
	{
		$query = new Miaox_Search_SphinxQl_Query();
		$actual = $query->select()->from( 'articles' )->compile()->getCompiled();
		$expected = 'SELECT * FROM `articles` ';

		$this->assertEquals( $expected, $actual );
	}
}