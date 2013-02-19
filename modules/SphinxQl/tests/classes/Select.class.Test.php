<?php
class Miaox_SphinxQl_Select_Test extends PHPUnit_Framework_TestCase
{
	public function testSelectSimple()
	{
		$obj = new Miaox_SphinxQl_Query();

		$obj->select( 'attr1' )->from( array(
			'index1' ) );
		$actual = $obj->compile()->getCompiled();

		$expected = 'SELECT `attr1` FROM `index1`';
		$this->assertEquals( $expected, $actual );
	}

	public function testSelectWithFunc()
	{
		$obj = new Miaox_SphinxQl_Query();

		$obj->select( 'attr1', 'GEODIST( lat, lng, 0.745194, 0.407116 ) AS geodist' )->from( array(
				'index1' ) );
		$actual = $obj->compile()->getCompiled();

		$expected = 'SELECT `attr1`, GEODIST( lat, lng, 0.745194, 0.407116 ) AS geodist FROM `index1`';
		$this->assertEquals( $expected, $actual );

		$obj->select( 'attr1', 'attr2 as mmm' )->from( array(
				'index1' ) );
		$actual = $obj->compile()->getCompiled();

		$expected = 'SELECT `attr1`, attr2 as mmm FROM `index1`';
		$this->assertEquals( $expected, $actual );
	}
}