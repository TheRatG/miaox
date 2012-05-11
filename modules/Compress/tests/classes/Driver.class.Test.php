<?php
class Miaox_Compress_Driver_Test extends PHPUnit_Framework_TestCase
{
	protected $_sourceDir;
	protected $_tmpDir;

	public function setUp()
	{
		$this->_sourceDir = Miao_PHPUnit::getSourceFolder( __CLASS__ );

		$this->_tmpDir = $this->_sourceDir . '/' . 'tmp';
		if ( !file_exists( $this->_tmpDir ) )
		{
			mkdir( $this->_tmpDir );
		}
	}

	public function tearDown()
	{
		Miao_PHPUnit::rmdirr( $this->_tmpDir );
	}

	/**
	 *
	 * @dataProvider providerTestMergeFile
	 */
	public function testMergeFile( $files, $actualFilename, $exceptionName = '' )
	{
		$log = Miao_Log::easyFactory( $this->_tmpDir . '/' . 'test_merge_file_log' );

		$obj = new Miaox_Compress_Driver( $log );

		foreach ( $files as &$file )
		{
			$file = $this->_sourceDir . $file;
		}
		$outpuFilename = $this->_tmpDir . '/total';
		$actualFilename = $this->_sourceDir . $actualFilename;
		$obj->mergeFiles( $files, $outpuFilename );

		$this->assertFileEquals( $outpuFilename, $actualFilename );
	}

	public function providerTestMergeFile()
	{
		$data = array();

		$data[] = array( array( '/1/1.js', '/1/2.js', '/1/3.js' ), '/1/actual.js' );

		return $data;
	}
}