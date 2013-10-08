<?php
class Miaox_File_Test extends PHPUnit_Framework_TestCase
{

	/**
	 *
	 * @dataProvider providerTestGetExtension
	 */
	public function testGetExtension( $filename, $actual )
	{
		$sourceDir = Miao_PHPUnit::getSourceFolder( __METHOD__ );
		$filename = $sourceDir . $filename;

		$expected = Miaox_File::getExtension( $filename );
		$this->assertEquals( $expected, $actual );
	}

	public function providerTestGetExtension()
	{
		$data = array();

		$data[] = array( '/1.jpg', 'jpg' );
		$data[] = array( '/empty', '' );
		$data[] = array( '/nopng.png', 'jpg' );
		$data[] = array( '/phpKSORWo', 'jpg' );
		$data[] = array( '/simple.txt', 'txt' );

		return $data;
	}
}