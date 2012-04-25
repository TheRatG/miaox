<?php
class Miaox_Aop_AopUseTest extends PHPUnit_Framework_TestCase
{
	private $_aop_source_path = '';
	private $_aop_obj = null;
	private $_path_tmp;

	public function setUp()
	{
		$uniora_modules_root = realpath( dirname( __FILE__ ) . '/../../../../' );
		$this->_aop_source_path = $uniora_modules_root . '/modules/Aop/tests/sources';
		$this->_aop_obj = new Miaox_Aop_Client();
		$this->_path_tmp = $uniora_modules_root . '/tmp';
	}

	public function tearDown()
	{
		$path = $this->_path_tmp . '/AopSkeleton*';
		shell_exec( sprintf( 'rm -rf %s', $path ) );
	}

	public function testBeforeAfterAll()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aop_obj->requireFile( $this->_path_tmp . '/' . $class_name . '.class.php', $this->_aop_source_path . '/before_after_all.xml' );

		ob_start();
		$aoped_obj = new $class_name();
		echo '-';
		$aoped_obj->show1();
		echo '-';
		$aoped_obj->show2();
		$res = ob_get_clean();
		$this->assertEquals( $res, '13-113-123' );
	}

	public function atestBeforeAfterFunctionClass()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aop_obj->requireFile( Uniora_Core::buildPath( $this->_path_tmp, $class_name . '.class.php' ), Uniora_Core::buildPath( $this->_aop_source_path, 'before_after_fc.xml' ) );

		ob_start();
		$aoped_obj = new $class_name();
		echo '-';
		$aoped_obj->show1();
		echo '-';
		$aoped_obj->show2();
		$res = ob_get_clean();
		$this->assertEquals( $res, '-11-23' );
	}

	public function atestAroundAll()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aop_obj->requireFile( Uniora_Core::buildPath( $this->_path_tmp, $class_name . '.class.php' ), Uniora_Core::buildPath( $this->_aop_source_path, 'around_all.xml' ) );

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

	public function atestAroundFunctionClass()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aop_obj->requireFile( Uniora_Core::buildPath( $this->_path_tmp, $class_name . '.class.php' ), Uniora_Core::buildPath( $this->_aop_source_path, 'around_fc.xml' ) );

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

	public function atestNameAll()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aop_obj->requireFile( Uniora_Core::buildPath( $this->_path_tmp, $class_name . '.class.php' ), Uniora_Core::buildPath( $this->_aop_source_path, 'name_all.xml' ) );

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

	public function atestNameFunctionClass()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aop_obj->requireFile( Uniora_Core::buildPath( $this->_path_tmp, $class_name . '.class.php' ), Uniora_Core::buildPath( $this->_aop_source_path, 'name_fc.xml' ) );

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

	public function atestNameNotFunctionClass()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aop_obj->requireFile( Uniora_Core::buildPath( $this->_path_tmp, $class_name . '.class.php' ), Uniora_Core::buildPath( $this->_aop_source_path, 'name_nfc.xml' ) );
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

	public function atestDoubleXml()
	{
		$class_name = $this->_getCurrentClassName( __METHOD__ );
		$this->_aop_obj->requireFile( Uniora_Core::buildPath( $this->_path_tmp, $class_name . '.class.php' ), array(
			Uniora_Core::buildPath( $this->_aop_source_path, 'name_double1.xml' ),
			Uniora_Core::buildPath( $this->_aop_source_path, 'name_double2.xml' ) ) );

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

		$skeleton_text = file_get_contents( $this->_aop_source_path . '/AopSkeleton.class.php' );
		$skeleton_text = str_replace( 'class Miaox_AopSkeleton', 'class ' . $new_class_name, $skeleton_text );
		file_put_contents( $this->_path_tmp . $new_class_name . '.class.php', $skeleton_text );

		return $new_class_name;
	}
}
