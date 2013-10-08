<?php
class Miaox_File_Upload_Test extends PHPUnit_Framework_TestCase
{

	public function testMain()
	{
		$baseDir = Miao_PHPUnit::getSourceFolder( __CLASS__ ) ;
		$filename = 'hypnolarge.gif';
		$file = $baseDir . '/' . $filename;

		$obj = new Miaox_File_Upload( $baseDir . '/tmp' );
		$res = $obj->run( $file );

		$this->assertTrue( file_exists( $res ) );
		unlink( $res );
		Miao_PHPUnit::rmdirr( $baseDir . '/tmp' );
	}

	public function testGenerateFilename()
	{
		$baseDir = Miao_PHPUnit::getSourceFolder( __CLASS__ );
		$filename = 'hypnolarge.gif';
		$file = $baseDir . '/' . $filename;
		$obj = new Miaox_File_Upload( $baseDir );
		$res = $obj->getFilename( $file );
		$this->assertEquals( $baseDir . '/88/86/' . Miaox_File::hash( $file ) . '.gif', $res );
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

	public function testRunByFiles()
	{
		$sourceDir = Miao_PHPUnit::getSourceFolder( __METHOD__ );
		$files = array(
			'picture1' => array(
				'name' => '1.jpg',
				'type' => 'image/jpeg',
				'tmp_name' => $sourceDir . '/1.jpg',
				'error' => 0,
				'size' => 1813815 ),
			'picture2' => array(
				'name' => '2.jpg',
				'type' => 'image/jpeg',
				'tmp_name' => $sourceDir . '/2.jpg',
				'error' => 0,
				'size' => 1923271 ) );
		$baseDir = Miao_PHPUnit::getSourceFolder( __CLASS__ ) . '/tmp';
		$obj = new Miaox_File_Upload( $baseDir );
		$expected = $obj->runByFiles( $files );

		$actual = array(
			'picture1' => $baseDir . '/06/44/b42029b988db920ed3f643d5c0151385.jpg',
			'picture2' => $baseDir . '/22/46/b248310e95bf9930c1a5475abecced20.jpg' );
		$this->assertEquals( $expected, $actual );
		Miao_PHPUnit::rmdirr( $baseDir );
	}
}