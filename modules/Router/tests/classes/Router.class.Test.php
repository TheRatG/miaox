<?php
class Miaox_Router_Test extends PHPUnit_Framework_TestCase
{
	/**
     * @var Miaox_Router
     */
	protected $_router;

	function setUp()
	{
		$this->_router = Miaox_Router::getInstance();
	}

	public function testContainer()
	{
		$config = $this->_getConfig();
		$this->_router->clearRoutes();
		$this->_router->loadRoutesFromArray( $config );
		$this->assertCount( 4, $this->_router->getRoutes() );
	}

	/**
     * @expectedException Miaox_Router_Exception
     */
	public function testEmptyParamException()
	{
		$this->_router->getRoute();
	}

	public function testRoute()
	{
		$config = $this->_getConfig();

		$this->_router->clearRoutes();
		$this->_router->loadRoutesFromArray( $config );

		$route = $this->_router->getRoute( '/done/test/123/aaaa' );
		$this->assertEquals( 'TestView', $route->getView() );

		$this->_router->clearRoutes();
		$this->_router->loadRoutesFromArray( $config );

		$route = $this->_router->getRoute( '/load/test' );
		$this->assertEquals( 'TestAction', $route->getAction() );
	}

	/**
     * @expectedException Miaox_Router_Exception_RouteNotFound
     */
	public function testNotFound()
	{
		$this->_router->clearRoutes();
		$this->_router->loadRoutesFromArray( $this->_getConfig() );

		$route = $this->_router->getRoute( '/done/test' );
		$this->assertEquals( 'TestView', $route->getView() );
	}

	/**
     * @expectedException Miaox_Router_Exception_RouteNotFound
     */
	public function testRouteException()
	{
		$config = $this->_getConfig();

		$this->_router->clearRoutes();
		$this->_router->loadRoutesFromArray( $config );

		$route = $this->_router->getRoute( '/social/test10/111' );
		$this->assertNotNull( $route );
	}

	public function testRouteParams()
	{
		$config = $this->_getConfig();

		$this->_router->clearRoutes();
		$this->_router->loadRoutesFromArray( $config );
		$this->_router->getRoute( '/save/test/10' );
		$this->assertEquals( 'test', $this->_router->getParam( 'social' ) );
		$this->assertEquals( '10', $this->_router->getParam( 'id' ) );

		$this->_router->clearRoutes();
		$this->_router->loadRoutesFromArray( $config );
		$this->_router->getRoute( '/save/test' );
		$this->assertEquals( 'test', $this->_router->getParam( 'social' ) );
		$this->assertNull( $this->_router->getParam( 'id' ) );
	}

	public function testSimpleRoute()
	{
		$this->_router->clearRoutes();
		$this->_router->loadRoutesFromArray( $this->_getConfig() );
		$route = $this->_router->getRoute( '/' );
		$this->assertEquals( 'Main', $route->getView() );
		$this->assertNull( $this->_router->getParam( 'test' ) );
		$this->assertEmpty( $this->_router->getParam() );
	}

	public function testUrlGeneration()
	{
		$this->_router->clearRoutes();
		$this->_router->loadRoutesFromArray( $this->_getConfig() );

		$this->assertEquals( '/', $this->_router->genUrlByView( 'Main' ) );
		$ret = $this->_router->genUrlByView( 'TestView', array(
			'social' => 123,
			'var' => 123,
			'id' => 123 ) );
		$this->assertEquals( '/done/123/123/123', $ret );

		$ret = $this->_router->genUrlByView( 'TestView', array(
			'social' => 123,
			'var' => 123,
			'id' => 'aaa' ) );

		$this->assertEquals( '', $ret );

		$ret = $this->_router->genUrlByAction( 'Test', array(
			'social' => 'test',
			'id' => 'aaa' ) );

		$this->assertEquals( '/save/test/aaa', $ret );

		$ret = $this->_router->genUrlByAction( 'Test', array(
			'social' => 'test3',
			'id' => 'aaa' ) );

		$this->assertEquals( '', $ret );

		$ret = $this->_router->genUrlByView( 'TestView', array(
			'social' => 'test10',
			///            'var' => 123,
			'id' => 123 ) );
		$this->assertEquals( '', $ret );

		$ret = $this->_router->genUrlByAction( 'TestAction', array(
			'social' => 'test',
			'id' => 123 ) );

		$this->assertEquals( '/load/test/123', $ret );

		$ret = $this->_router->genUrlByAction( 'TestAction', array(
			'social' => 'test' ) );

		$this->assertEquals( '/load/test/', $ret );
	}

	public function test404Error()
	{
		$this->_router->clearRoutes();
		$this->_router->loadRoutesFromArray( $this->_getConfig() );
		$this->assertEquals( '404', $this->_router->getErrorRoute( '404' )->getView() );
	}

	/**
     * @expectedException Miaox_Router_Exception_RouteNotFound
     */
	public function testEmpty404Error()
	{
		$this->_router->clearRoutes();
		$cfg = $this->_getConfig();
		unset( $cfg[ 'error' ] );
		$this->_router->loadRoutesFromArray( $cfg );
		$this->assertEquals( '404', $this->_router->getErrorRoute( '404' )->getView() );
	}

	public function testSimilarRoutes()
	{
		$cfg = array(
			'error' => array(
				'code' => '404',
				'view' => '404' ),
			'route' => array(
				array(
					'url' => '/',
					'view' => 'Main' ),
				array(
					'url' => '/photo',
					'view' => 'Photo_List' ),
				array(
					'url' => '/photo/:section',
					'view' => 'Photo_List',
					'validator' => array(
						'type' => 'Regexp',
						'param' => 'section',
						'regexp' => '[a-zA-Z_]+' ) ),
				array(
					'url' => '/photo/:page',
					'view' => 'Photo_List',
					'validator' => array(
						'type' => 'Regexp',
						'param' => 'page',
						'regexp' => 'p([0-9]+)' ) ),
				array(
					'url' => '/photo/:section/:page',
					'view' => 'Photo_List',
					'validator' => array(
						array(
							'type' => 'Regexp',
							'param' => 'page',
							'regexp' => 'p([0-9]+)' ),
						array(
							'type' => 'Regexp',
							'param' => 'section',
							'regexp' => '[a-zA-Z_]+' ) ) ) ) );
		$this->_router->clearRoutes();
		$this->_router->loadRoutesFromArray( $cfg );
	}

	protected function _getConfig()
	{
		return array(
			'error' => array(
				'code' => '404',
				'view' => '404' ),
			'route' => array(
				0 => array(
					'url' => '/',
					'view' => 'Main' ),
				1 => array(
					'url' => '/save/:social/:id',
					'action' => 'Test',
					'validator' => array(
						0 => array(
							'type' => 'regexp',
							'param' => 'social',
							'regexp' => '^(test|test2)$' ) ) ),
				2 => array(
					'url' => '/load/:social/:id',
					'action' => 'TestAction',
					'validator' => array(
						'type' => 'regexp',
						'param' => 'social',
						'regexp' => '(test|test2)' ) ),
				3 => array(
					'url' => '/done/:social/:id/:var',
					'view' => 'TestView',
					'validator' => array(
						0 => array(
							'type' => 'NotEmpty',
							'param' => 'social' ),
						1 => array(
							'type' => 'Numeric',
							'param' => 'id' ),
						2 => array(
							'param' => 'var' ) ) ) ) );
	}
}