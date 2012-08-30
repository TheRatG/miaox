<?php
class Miaox_File_Upload_Test extends PHPUnit_Framework_TestCase
{

	public function atestMain()
	{
		$baseDir = Miao_PHPUnit::getSourceFolder( __CLASS__ );
		$filename = 'hypnolarge.gif';
		$file = $baseDir . '/' . $filename;

		$obj = new Miaox_File_Upload( $baseDir );
		$res = $obj->run( $file );

		$this->assertEquals( $baseDir, $res );
	}

	public function testGenerateFilename()
	{
		$baseDir = Miao_PHPUnit::getSourceFolder( __CLASS__ );
		$filename = 'hypnolarge.gif';
		$file = $baseDir . '/' . $filename;
		$obj = new Miaox_File_Upload( $baseDir );
		$res = $obj->getFilename( $file, 1, 1 );
		$this->assertEquals( $baseDir . '/1/1/' . Miaox_File::hash( $file ) . '.gif', $res );
	}

	/**
	 * @dataProvider providerTestAddDir
	 */
	public function testAddDir( $hash, $cntSubDir, $cntChar, $actual )
	{
		$baseDir = Miao_PHPUnit::getSourceFolder( __CLASS__ );
		$obj = new Miaox_File_Upload( $baseDir );

		$expected = $obj->getAddDirByHash( $hash, $cntSubDir, $cntChar );

		$this->assertEquals( $expected, $actual );
	}

	public function providerTestAddDir()
	{
		$data = array();

		$data[] = array( '1234', 1, 1, '/4' );
		$data[] = array( '4', 1, 1, '/4' );

		$data[] = array( '1', 2, 1, '/0/1' );
		$data[] = array( '1234', 2, 3, '/001/234' );

		$data[] = array( '1234', 2, 1, '/3/4' );
		$data[] = array( '1234', 2, 1, '/3/4' );
		$data[] = array( '1234', 2, 2, '/12/34' );


		$data[] = array( 'a', 2, 2, '/00/10' );

		return $data;
	}

	/**
	 *
	 * @dataProvider providerTestGetExt
	 */
	public function testGetExt( $filename, $actual )
	{
		$expected = Miaox_File::getExtension( $filename );
		$this->assertEquals( $expected, $actual );
	}

	public function providerTestGetExt()
	{
		$data = array();

		$baseDir = Miao_PHPUnit::getSourceFolder( __CLASS__ );
		$data[] = array( $baseDir . '/text.txt', 'txt' );

		return $data;
	}

	public function testGetHash()
	{
		$baseSharedDir = Miao_PHPUnit::getSourceFolder( __CLASS__ );
		$filename = $baseSharedDir . '/test_get_hash.txt';

		file_put_contents( $filename, '1' );
		$hash1 = Miaox_File::hash( $filename );

		$hash2 = file_put_contents( $filename, '22' );
		$hash2 = Miaox_File::hash( $filename );

		$this->assertFalse( $hash1 == $hash2 );

		unlink( $filename );
	}

	/**
	 * @dataProvider providerTestConvert
	 */
	public function testConvert( $string, $actual )
	{
		$expected = Miaox_File::convert( $string );
		$this->assertEquals( $expected, $actual );
	}

	public function providerTestConvert()
	{
		$data = array();

		$data[] = array( '0', "0" );
		$data[] = array( 'a', "10" );
		$data[] = array( 'z', "35" );
		$data[] = array( '10', "36" );
		$data[] = array( 'aa', "370" );
		return $data;
	}
}