<?php
/*
 * Don't forget run ./scripts/sphinx.sh start
*/
class Miaox_Search_SphinxQl_Test extends PHPUnit_Framework_TestCase
{
	protected $_sphinxql;

	public function setUp()
	{
		$this->_sphinxql = new Miaox_Search_SphinxQl( '127.0.0.1', 4499 );
	}

	public function tearDown()
	{
		unset( $this->_sphinxql );
	}

	public function testSelect()
	{
		$spinxql = $this->_sphinxql;
		$actual = $spinxql->select( 'id' )->from( 'articles' )->execute();

		$expected = array(
			0 => array(
				'id' => '1' ),
			1 => array(
				'id' => '2' ),
			2 => array(
				'id' => '3' ),
			3 => array(
				'id' => '562949985559532' ),
			4 => array(
				'id' => '562949985559552' ),
			5 => array(
				'id' => '562949985560752' ) );

		$this->assertEquals( $expected, $actual );
	}

	public function testWhere()
	{
		$spinxql = $this->_sphinxql;
		$actual = $spinxql->select( 'id' )->from( 'articles' )->where( 'id', '=', 1 )->execute();
		$expected = array(
			0 => array(
				'id' => '1' ) );

		$this->assertEquals( $expected, $actual );
	}

	public function testSelectRow()
	{
		$spinxql = $this->_sphinxql;
		$actual = $spinxql->select( 'id' )->from( 'articles' )->compile()->getCompiled();
		$expected = 'SELECT `id` FROM `articles`';

		$this->assertEquals( $expected, $actual );

		$spinxql = $this->_sphinxql;
		$actual = $spinxql->select( 'group' )->from( 'articles' )->compile()->getCompiled();
		$expected = 'SELECT `group` FROM `articles`';

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * WHERE `column` = 'value'
	 * ->where('column', 'value');
	 */
	public function testWhereSimple()
	{
		$spinxql = $this->_sphinxql;
		$actual = $spinxql->select( 'id' )->from( 'articles' )->where( 'id', 1 );
		$actual = $spinxql->compile()->getCompiled();
		$expected = 'SELECT `id` FROM `articles` WHERE id = 1';

		$this->assertEquals( $expected, $actual );

		$spinxql = $this->_sphinxql;
		$actual = $spinxql->select()->from( 'articles' )->where( 'group', 1 );
		$actual = $spinxql->compile()->getCompiled();
		$expected = 'SELECT * FROM `articles` WHERE `group` = 1';

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * WHERE `column` >= 'value'
	 * ->where('column', '=', 'value');
	 */
	public function testWhereOperator()
	{
		$spinxql = $this->_sphinxql;
		$actual = $spinxql->select( 'id' )->from( 'articles' )->where( 'id', '>=', 1 );
		$actual = $spinxql->compile()->getCompiled();
		$expected = 'SELECT `id` FROM `articles` WHERE id >= 1';

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * WHERE `column` IN ('value1', 'value2', 'value3')
	 * ->where('column', 'IN', array('value1', 'value2', 'value3'));
	 */
	public function testWhereIn()
	{
		$spinxql = $this->_sphinxql;
		$actual = $spinxql->select( 'id' )->from( 'articles' )->where( 'id', 'IN', array(
			1,
			2,
			3 ) );
		$actual = $spinxql->compile()->getCompiled();
		$expected = "SELECT `id` FROM `articles` WHERE id IN (1, 2, 3)";

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * WHERE `column` BETWEEN 'value1' AND 'value2'
	 * ->where('column', 'BETWEEN', array('value1', 'value2'))
	 */
	public function testWhereBetween()
	{
		$spinxql = $this->_sphinxql;
		$actual = $spinxql->select( 'id' )->from( 'articles' )->where( 'id', 'BETWEEN', array(
			1,
			3 ) );
		$actual = $spinxql->compile()->getCompiled();
		$expected = "SELECT `id` FROM `articles` WHERE `id` BETWEEN 1 AND 3";

		$this->assertEquals( $expected, $actual );
	}

	public function testOrder()
	{
		$spinxql = $this->_sphinxql;
		$actual = $spinxql->select()->from( 'articles' )->orderBy( 'group', Miaox_Search_SphinxQl::ORDER_ASC )->limit( 1 );
		$actual = $spinxql->execute();
		$expected = array(
			0 => array(
				'id' => '1',
				'published' => '1132223498',
				'group' => '45' ) );
		$this->assertEquals( $expected, $actual );

		$actual = $spinxql->getCompiled();
		$expected = "SELECT * FROM `articles` ORDER BY `group` ASC LIMIT 0, 1";
		$this->assertEquals( $expected, $actual );
	}
}