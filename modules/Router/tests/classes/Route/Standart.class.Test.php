<?php
/**
 *
 *
 * @package
 * @subpackage
 * @author Alexander Rodionov <avrodionov@rbc.ru>
 * $Id$
 */
class Miaox_Router_Route_Standart_Test extends PHPUnit_Framework_TestCase
{
    function testGetterSetters()
    {
        $route = new Miaox_Router_Route_Standart('/test');
        $actionName = 'action';
        $viewName = 'view';
        $route->setAction($actionName);
        $route->setView($viewName);
        $this->assertEquals($actionName, $route->getAction());
        $this->assertEquals($viewName, $route->getView());
    }

    function testParams()
    {
        $route = new Miaox_Router_Route_Standart('/test/:first/:second');
        $params = $route->getParams();
        $this->assertArrayHasKey('first', $params);
        $this->assertArrayHasKey('second', $params);
        $this->assertInstanceOf('Miaox_Router_Route_Param', $params['first']);
        $this->assertEquals('second', $params['second']->getName());
    }

    function testParamValidators()
    {
        $route = new Miaox_Router_Route_Standart('/test/:first/:second');
        $validators = array (
            array (
            'param' => 'first',
            'regexp' => '^(test|test2)$'
            ),
            array (
                'param' => 'second',
                'regexp' => '^\d+$'
            ),
            array (
                'param' => 'second',
                'regexp' => '^\d{2}$'
            ),
        );

        $route->setValidators($validators);
        $params = $route->getParams();
        $this->assertTrue($params['first']->isValid('test'));
        $this->assertFalse($params['first']->isValid('test1'));
        $this->assertTrue($params['second']->isValid('10'));
    }

    function testIsUriValid()
    {
        $route = new Miaox_Router_Route_Standart('/test/:first/:second');
        $this->assertTrue($route->isUriValid('/test/123/345'));
    }

}
