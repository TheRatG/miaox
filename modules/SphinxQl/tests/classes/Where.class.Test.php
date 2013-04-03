<?php
/**
 * Where.class.Test.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 28.03.13 9:05
 */
require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_Where_Test extends Miaox_SphinxQl_Helper_Test
{
    /**
     * @var Miaox_SphinxQl
     */
    private $_sphinxQl;

    public function setUp()
    {
        $this->_sphinxQl = new Miaox_SphinxQl( SEARCHD_HOST, SEARCHD_PORT );
    }

    public function tearDown()
    {
        unset( $this->_sphinxQl );
    }

    /**
     * WHERE `column` = 'value'
     * ->where('column', 'value');
     */
    public function testWhereSimple()
    {
        $query = $this->_sphinxQl;
        $actual = $query
            ->select( 'id' )
            ->from( 'articles' )
            ->where( 'id', 1 );
        $actual = $query
            ->compile()
            ->getCompiled();
        $expected = 'SELECT `id` FROM `articles` WHERE id = 1';

        $this->assertEquals( $expected, $actual );

        $query = $this->_sphinxQl;
        $actual = $query
            ->select()
            ->from( 'articles' )
            ->where( 'group', 1 );
        $actual = $query
            ->compile()
            ->getCompiled();
        $expected = 'SELECT * FROM `articles` WHERE `group` = 1';

        $this->assertEquals( $expected, $actual );
    }

    /**
     * WHERE `column` >= 'value'
     * ->where('column', '=', 'value');
     */
    public function testWhereOperator()
    {
        $query = $this->_sphinxQl;
        $actual = $query
            ->select( 'id' )
            ->from( 'articles' )
            ->where( 'id', '>=', 1 );
        $actual = $query
            ->compile()
            ->getCompiled();
        $expected = 'SELECT `id` FROM `articles` WHERE id >= 1';

        $this->assertEquals( $expected, $actual );
    }

    /**
     * WHERE `column` IN ('value1', 'value2', 'value3')
     * ->where('column', 'IN', array('value1', 'value2', 'value3'));
     */
    public function testWhereIn()
    {
        $query = $this->_sphinxQl;
        $actual = $query
            ->select( 'id' )
            ->from( 'articles' )
            ->where(
            'id', 'IN', array(
                             1,
                             2,
                             3
                        )
        );
        $actual = $query
            ->compile()
            ->getCompiled();
        $expected = "SELECT `id` FROM `articles` WHERE id IN ( 1, 2, 3 )";

        $this->assertEquals( $expected, $actual );
    }

    /**
     * WHERE `column` BETWEEN 'value1' AND 'value2'
     * ->where('column', 'BETWEEN', array('value1', 'value2'))
     */
    public function testWhereBetween()
    {
        $query = $this->_sphinxQl;
        $actual = $query
            ->select( 'id' )
            ->from( 'articles' )
            ->where(
            'id', 'BETWEEN', array(
                                  1,
                                  3
                             )
        );
        $actual = $query
            ->compile()
            ->getCompiled();
        $expected = "SELECT `id` FROM `articles` WHERE id BETWEEN 1 AND 3";

        $this->assertEquals( $expected, $actual );
    }
}
