<?php
class Miaox_Exceptionizer_Test extends PHPUnit_Framework_TestCase
{

	public function testMain()
	{
		$this->setExpectedException( 'E_WARNING' );
		$exceptionizer = new Miaox_Exceptionizer( E_ALL );
		$x = 8 / 0;
		unset( $exceptionizer );
	}
}