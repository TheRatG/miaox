<?php
/*
 * Don't forget run ./scripts/sphinx.sh start
*/
class Miaox_Search_SphinxQl_Test extends PHPUnit_Framework_TestCase
{
	public function testSelect()
	{
		$search = new Miaox_Search_SphinxQl();
		$search->select()->from( 'articles' )->execute();
	}
}