<?php
/*
 * Don't forget run ./scripts/sphinx.sh start
*/
class Miaox_SphinxQl_Query_Test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_query = new Miaox_SphinxQl( '127.0.0.1', 4499 );
	}

	public function testSelect()
	{
		$query = $this->_query;
		$actual = $query->select()->from( 'articles' )->compile()->getCompiled();
		$expected = 'SELECT * FROM `articles`';

		$this->assertEquals( $expected, $actual );
	}

	public function testSelectRow()
	{
		$query = $this->_query;
		$actual = $query->select( 'id' )->from( 'articles' )->compile()->getCompiled();
		$expected = 'SELECT `id` FROM `articles`';

		$this->assertEquals( $expected, $actual );

		$query = $this->_query;
		$actual = $query->select( 'group' )->from( 'articles' )->compile()->getCompiled();
		$expected = 'SELECT `group` FROM `articles`';

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * WHERE `column` = 'value'
	 * ->where('column', 'value');
	 */
	public function testWhereSimple()
	{
		$query = $this->_query;
		$actual = $query->select( 'id' )->from( 'articles' )->where( 'id', 1 );
		$actual = $query->compile()->getCompiled();
		$expected = 'SELECT `id` FROM `articles` WHERE id = 1';

		$this->assertEquals( $expected, $actual );

		$query = $this->_query;
		$actual = $query->select()->from( 'articles' )->where( 'group', 1 );
		$actual = $query->compile()->getCompiled();
		$expected = 'SELECT * FROM `articles` WHERE `group` = 1';

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * WHERE `column` >= 'value'
	 * ->where('column', '=', 'value');
	 */
	public function testWhereOperator()
	{
		$query = $this->_query;
		$actual = $query->select( 'id' )->from( 'articles' )->where( 'id', '>=', 1 );
		$actual = $query->compile()->getCompiled();
		$expected = 'SELECT `id` FROM `articles` WHERE id >= 1';

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * WHERE `column` IN ('value1', 'value2', 'value3')
	 * ->where('column', 'IN', array('value1', 'value2', 'value3'));
	 */
	public function testWhereIn()
	{
		$query = $this->_query;
		$actual = $query->select( 'id' )->from( 'articles' )->where( 'id', 'IN', array(
			1,
			2,
			3 ) );
		$actual = $query->compile()->getCompiled();
		$expected = "SELECT `id` FROM `articles` WHERE id IN (1, 2, 3)";

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * WHERE `column` BETWEEN 'value1' AND 'value2'
	 * ->where('column', 'BETWEEN', array('value1', 'value2'))
	 */
	public function testWhereBetween()
	{
		$query = $this->_query;
		$actual = $query->select( 'id' )->from( 'articles' )->where( 'id', 'BETWEEN', array(
			1,
			3 ) );
		$actual = $query->compile()->getCompiled();
		$expected = "SELECT `id` FROM `articles` WHERE `id` BETWEEN 1 AND 3";

		$this->assertEquals( $expected, $actual );
	}
}