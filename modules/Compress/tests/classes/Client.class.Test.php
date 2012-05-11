<?php
class Miaox_Compress_Client_Test extends PHPUnit_Framework_TestCase
{
	private $_sourceDir;
	private $_dstDir;
	private $_client;

	public function setUp()
	{
		$this->_sourceDir = Miao_PHPUnit::getSourceFolder( __CLASS__ );
		$this->_dstDir = $this->_sourceDir . '/tmp';

		if ( !file_exists( $this->_dstDir ) )
		{
			mkdir( $this->_dstDir );
		}

		$this->_client = new Miaox_Compress_Client( $this->_dstDir );
	}

	public function tearDown()
	{
		Miao_PHPUnit::rmdirr( $this->_dstDir );
	}

	/**
	 * @dataProvider providerTestCss
	 */
	public function testCss( $fileList, $compress, $actualList, $enabled = true, $exceptionName = '' )
	{
		$this->_client->setEnabled( $enabled );
		$expectedList = $this->_client->getCss( $fileList, $compress );

		$this->assertEquals( $expectedList, $actualList );
	}

	public function providerTestCss()
	{
		$this->setUp();

		$data = array();

		$sourceDir = Miao_PHPUnit::getSourceFolder( __METHOD__ );
		$filenameList = array( $sourceDir . '/1.css' );
		$data[] = array(
			$filenameList,
			true,
			array(
				Miaox_Compress::makeFilename( $this->_dstDir, $filenameList ) ) );
		$data[] = array( $filenameList, false, $filenameList, false );
		$data[] = array( $filenameList, false, $filenameList, true );

		return $data;
	}

	/**
	 * @dataProvider providerTestJs
	 */
	public function testJs( $fileList, $compress, $actualList, $enabled = true, $exceptionName = '' )
	{
		if ( !empty( $exceptionName ) )
		{
			$this->setExpectedException( $exceptionName );
		}

		$this->_client->setEnabled( $enabled );
		$expectedList = $this->_client->getJs( $fileList, $compress );

		$this->assertEquals( $expectedList, $actualList );
	}

	public function providerTestJs()
	{
		$this->setUp();

		$data = array();

		$sourceDir = Miao_PHPUnit::getSourceFolder( __METHOD__ );
		$filenameList = array( $sourceDir . '/1.js' );
		$data[] = array(
			$filenameList,
			true,
			array(
				Miaox_Compress::makeFilename( $this->_dstDir, $filenameList ) ) );
		$data[] = array( $filenameList, false, $filenameList, false );
		$data[] = array( $filenameList, false, $filenameList, true );

		// test rel path
		$filenameList = array( $sourceDir . '/1.js', '../testJs/2.js' );
		$data[] = array(
			$filenameList,
			true,
			array(
				Miaox_Compress::makeFilename( $this->_dstDir, $filenameList ) ) );

		$filenameList = array( $sourceDir . '/ksajhdfkj.js' );
		$data[] = array(
			$filenameList,
			true,
			array(
				Miaox_Compress::makeFilename( $this->_dstDir, $filenameList ) ),
			true,
			'Miaox_Compress_Exception' );

		return $data;
	}
}