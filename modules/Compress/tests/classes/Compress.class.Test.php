<?php
class Miao_Compress_Test extends PHPUnit_Framework_TestCase
{
	protected $_moduleRoot;

	public function setUp()
	{
		$path = Miao_Path::getDefaultInstance();
		$this->_path = $path;

		$sourceDir = Miao_PHPUnit::getSourceFolder(
			'Miao_Compress_TestCompress_Test' );
		$moduleRoot = $path->getModuleRoot( 'Miao_TestCompress' );
		Miao_PHPUnit::copyr( $sourceDir, $moduleRoot );

		$this->_moduleRoot = $moduleRoot;
	}

	public function tearDown()
	{
		Miao_PHPUnit::rmdirr( $this->_moduleRoot );
	}

	/**
	 *
	 * @dataProvider providerTestConstruct
	 */
	public function testConstruct( $driverClassName, $exceptionName = '' )
	{
		if ( $exceptionName )
		{
			$this->setExpectedException( $exceptionName );
		}

		$options = array();
		$driver = new $driverClassName( $options );
		$compress = new Miao_Compress( $driver );
		$this->assertTrue( is_object( $compress ) );
	}

	public function providerTestConstruct()
	{
		$data = array();

		$exceptionName = 'Miao_Compress_Exception';

		$data[] = array( 'Miao_TestCompress_Driver_Invalid', $exceptionName );
		$data[] = array( 'Miao_TestCompress_Driver_Null', '' );

		return $data;
	}

	/**
	 *
	 * @dataProvider providerTestMakeFilename
	 */
	public function testMakeFilename( $dstFolder, $filenameList, $type, $exceptionName = '' )
	{
		$actual = $dstFolder . '/' . md5( implode( ':', $filenameList ) ) . '.js';
		$expected = Miao_Compress::makeFilename( $dstFolder, $filenameList );
		
		$this->assertEquals( $expected, $actual );
	}

	public function providerTestMakeFilename()
	{
		$data = array();

		$data[] = array( '/tmp/images', array( '1.js', '2.js', '3.js' ), '.js' );

		return $data;
	}
}