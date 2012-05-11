<?php
class Miaox_Compress_Driver_Yui_Test extends PHPUnit_Framework_TestCase
{
	protected $_moduleRoot;

	public function setUp()
	{
		$path = Miao_Path::getDefaultInstance();
		$this->_path = $path;

		$sourceDir = Miao_PHPUnit::getSourceFolder(
			'Miaox_Compress_TestCompress_Test' );
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
		$obj = new Miaox_Compress_Driver_Yui( $filename );

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
		$exceptionName = 'Miaox_Compress_Driver_Yui_Exception';
		$data[] = array( $moduleDir . '/data/yuicompressor.jar' );
		$data[] = array( '' );
		$data[] = array( 'asd.jar', $exceptionName );

		return $data;
	}


	/**
	 *
	 * @dataProvider providerTestSetOptions
	 * @param array $options
	 * @param unknown_type $exceptionName
	 */
	public function testSetOptions( array $options, $exceptionName = '' )
	{
		if ( !empty( $exceptionName ) )
		{
			$this->setExpectedException( $exceptionName );
		}

		$obj = new Miaox_Compress_Driver_Yui();
		$obj->setOptions( $options );
	}

	public function providerTestSetOptions()
	{
		$data = array();

		$exceptionName = 'Miaox_Compress_Driver_Yui_Exception';

		$data[] = array( array( 'type' => 'js' ), '' );
		$data[] = array( array( 'type' => 'css', 'v' => '' ), '' );
		$data[] = array( array( 'type' => 'img', 'v' => '' ), $exceptionName );

		$data[] = array( array( 'charset' => 'utf-8' ), $exceptionName );
		$data[] = array(
		array( 'charset' => 'utf-8', 'v' => '' ),
		$exceptionName );

		$data[] = array( array( 'type' => 'js', 'nomunge' => true ) );
		$data[] = array(
		array( 'type' => 'css', 'nomunge' => true ),
		$exceptionName );

		return $data;
	}

	/**
	 *
	 * @dataProvider providerTestMinify
	 */
	public function testMinify( $fileList, $dstFilename, $options, $actualFilename, $exceptionName = '' )
	{
		//$this->markTestSkipped( 'Miaox_Compress_Driver_Yui_Test test skipped, because work long time' );
		if ( !empty( $exceptionName ) )
		{
			$this->setExpectedException( $exceptionName );
		}

		$log = Miao_Log::easyFactory( $this->_moduleRoot . '/' . 'test_minify_log', false );
		$obj = new Miaox_Compress_Driver_Yui( '', $log );
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
		$options = array( 'charset' => 'utf-8', 'v' => true, 'type' => Miaox_Compress_Driver_Yui::TYPE_JS );
		$actualFilename = $sourceDir . '/actual_min_1.js';

		$data[] = array( $files, $dstFilename, $options, $actualFilename );

		return $data;
	}
}