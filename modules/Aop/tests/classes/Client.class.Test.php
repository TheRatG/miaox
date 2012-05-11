<?php
class Miaox_Aop_AopUseTest extends PHPUnit_Framework_TestCase
{
	private $_sourceDir = '';
	private $_aopObj = null;
	private $_tmp;

	public function setUp()
	{
		$this->_sourceDir = Miao_PHPUnit::getSourceFolder( 'Miaox_Aop_Test' );
		$this->_tmp = $this->_sourceDir . '/tmp';
		if ( !file_exists( $this->_tmp ) )
		{
			mkdir( $this->_tmp );
		}

		$config[ 'cache_dir' ] = $this->_tmp;
		$this->_aopObj = new Miaox_Aop_Client( $config );
	}

	public function tearDown()
	{
		Miao_PHPUnit::rmdirr( $this->_tmp );
	}

	public function testBeforeAfterAll()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aopObj->requireFile( $this->_tmp . '/' . $class_name . '.class.php', $this->_sourceDir . '/before_after_all.xml' );

		ob_start();
		$aoped_obj = new $class_name();
		echo '-';
		$aoped_obj->show1();
		echo '-';
		$aoped_obj->show2();
		$res = ob_get_clean();
		$this->assertEquals( $res, '13-113-123' );
	}

	public function testBeforeAfterFunctionClass()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aopObj->requireFile( $this->_tmp . '/' . $class_name . '.class.php', $this->_sourceDir . '/before_after_fc.xml' );

		ob_start();
		$aoped_obj = new $class_name();
		echo '-';
		$aoped_obj->show1();
		echo '-';
		$aoped_obj->show2();
		$res = ob_get_clean();
		$this->assertEquals( $res, '-11-23' );
	}

	public function testAroundAll()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aopObj->requireFile( $this->_tmp . '/' . $class_name . '.class.php', $this->_sourceDir . '/around_all.xml' );

		ob_start();
		$aoped_obj = new $class_name();
		echo '+';
		$aoped_obj->show1();
		echo '+';
		$aoped_obj->show1( 2 );
		echo '+';
		$aoped_obj->show2();
		$res = ob_get_clean();
		$this->assertEquals( $res, '11+111+11-k-1+121' );
	}

	public function testAroundFunctionClass()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aopObj->requireFile( $this->_tmp . '/' . $class_name . '.class.php', $this->_sourceDir . '/around_fc.xml' );

		$aoped_obj = new $class_name();

		ob_start();
		$aoped_obj->show1();
		echo '+';
		$aoped_obj->show1( 3 );
		$res = ob_get_clean();
		$this->assertEquals( $res, '<2+1-k-' );

		$res = $aoped_obj->show3();
		$this->assertEquals( $res, 4 );

		$res = $aoped_obj->show3( -2 );
		$this->assertEquals( $res, 1 );
	}

	public function testNameAll()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aopObj->requireFile( $this->_tmp . '/' . $class_name . '.class.php', $this->_sourceDir . '/name_all.xml' );

		$aoped_obj = new $class_name();

		ob_start();
		$aoped_obj->show1();
		echo '+';
		$aoped_obj->show1( 2 );
		echo '+';
		$aoped_obj->show2();
		$res = ob_get_clean();
		$this->assertEquals( $res, '11+111-k-+12' );
	}

	public function testNameFunctionClass()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aopObj->requireFile( $this->_tmp . '/' . $class_name . '.class.php', $this->_sourceDir . '/name_fc.xml' );

		$aoped_obj = new $class_name();

		ob_start();
		$aoped_obj->show1();
		$res = ob_get_clean();
		$this->assertEquals( $res, '10' );

		$res = $aoped_obj->show3( 10 );
		$this->assertEquals( $res, 14 );

		$res = $aoped_obj->show3( -10 );
		$this->assertEquals( $res, 9 );
	}

	public function testNameNotFunctionClass()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aopObj->requireFile( $this->_tmp . '/' . $class_name . '.class.php', $this->_sourceDir . '/name_nfc.xml' );
		$class_name2 = $class_name . 'Second';

		$aoped_obj = new $class_name();
		$aoped_obj2 = new $class_name2();

		ob_start();
		$aoped_obj->show1();
		echo '+';
		$aoped_obj->show2();
		echo '+';
		$aoped_obj2->show1();
		echo '+';
		$aoped_obj2->show2();
		$res = ob_get_clean();
		$this->assertEquals( $res, '1+1y2+4x+3yz' );
	}

	public function testDoubleXml()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aopObj->requireFile( $this->_tmp . '/' . $class_name . '.class.php', array(
			$this->_sourceDir . '/name_double1.xml',
			$this->_sourceDir . '/name_double2.xml' ) );

		$aoped_obj = new $class_name();

		ob_start();
		$aoped_obj->show1();
		$res = ob_get_clean();
		$this->assertEquals( $res, '123' );
	}

	private function _getCurrentClassName( $method_name )
	{
		$method_name = explode( '::', $method_name );
		$method_name = str_replace( 'test', '', $method_name[ 1 ] );

		$new_class_name = 'AopSkeleton' . $method_name;

		$skeleton_text = file_get_contents( $this->_sourceDir . '/AopSkeleton.class.php' );
		$skeleton_text = str_replace( 'class Miaox_AopSkeleton', 'class ' . $new_class_name, $skeleton_text );
		file_put_contents( $this->_tmp . '/' . $new_class_name . '.class.php', $skeleton_text );

		return $new_class_name;
	}
}
