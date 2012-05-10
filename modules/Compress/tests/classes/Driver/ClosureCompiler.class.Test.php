<?php
class Miao_Compress_Driver_ClosureCompiler_Test extends PHPUnit_Framework_TestCase
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
	 * @dataProvider providerTestSetJarFilename
	 */
	public function testSetJarFilename( $filename, $exceptionName = '' )
	{
		if ( !empty( $exceptionName ) )
		{
			$this->setExpectedException( $exceptionName );
		}
		$obj = new Miao_Compress_Driver_ClosureCompiler( $filename );

		$expected = $obj->getJarFilename();

		$this->assertTrue( file_exists( $expected ) );
		if ( !empty( $filename ) )
		{
			$this->assertEquals( $expected, $filename );
		}
	}

	public function providerTestSetJarFilename()
	{
		$data = array();

		$moduleDir = Miao_Path::getDefaultInstance()->getModuleRoot( __CLASS__ );
		$exceptionName = 'Miao_Compress_Driver_ClosureCompiler_Exception';
		$data[] = array( $moduleDir . '/data/compiler.jar' );
		$data[] = array( '' );
		$data[] = array( 'asd.jar', $exceptionName );

		return $data;
	}

	/**
	 *
	 * @dataProvider providerTestMinify
	 */
	public function testMinify( $fileList, $dstFilename, $options, $actualFilename, $exceptionName = '' )
	{
		if ( !empty( $exceptionName ) )
		{
			$this->setExpectedException( $exceptionName );
		}

		$log = Miao_Log::factory2( $this->_moduleRoot . '/' . 'test_minify_log', false );
		$obj = new Miao_Compress_Driver_ClosureCompiler( '', $log );
		$obj->minify( $fileList, $dstFilename, $options );
		$this->assertFileEquals( $dstFilename, $actualFilename );

		unlink( $dstFilename );
	}

	public function providerTestMinify()
	{
		$data = array();

		$sourceDir = Miao_PHPUnit::getSourceFolder( __METHOD__ );

		$files = array(	$sourceDir . '/hello.js', $sourceDir . '/goodbay.js' );
		$dstFilename = $sourceDir . '/expected_min_1.js';
		$options = array( 'charset' => 'utf-8', 'v' => true, 'type' => Miao_Compress_Driver_Yui::TYPE_JS );
		$actualFilename = $sourceDir . '/actual_min_1.js';

		$data[] = array( $files, $dstFilename, $options, $actualFilename );

		return $data;
	}
}