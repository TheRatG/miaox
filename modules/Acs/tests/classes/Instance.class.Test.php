<?php
class Miaox_Acs_Instance_Test extends PHPUnit_Framework_TestCase
{
	public function testEmpty()
	{
		$this->assertTrue( true );
	}

	public function testGetLog()
	{
		$actual = Miaox_Acs_Instance::log();
		$this->assertInstanceOf( 'Miao_Log', $actual );
	}

	public function testGetAdapter()
	{

	}
}