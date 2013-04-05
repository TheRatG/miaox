<?php
/**
 * Select.class.Test.php. Test generated query
 * @author: vpak <TheRatW@gmail.com>
 * @date: 26.03.13 11:19
 */
require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_Select_Test extends Miaox_SphinxQl_Helper_Test
{
    public function testSelectException()
    {
        $exceptionName = 'Miaox_SphinxQl_Query_Exception';
        $this->setExpectedException( $exceptionName );

        $query = $this->_sphinxQl;
        $actual = $query
            ->select()
            ->compile();
    }

    public function testSelectOne()
    {
        $query = $this->_sphinxQl;
        $actual = $query
            ->select()
            ->from( 'articles' )
            ->compile();
        $expected = 'SELECT * FROM `articles`';

        $this->assertEquals( $expected, $actual );
    }

    public function testSelectSystemAttribute()
    {
        $query = $this->_sphinxQl;
        $actual = $query
            ->select( '@id' )
            ->from( 'articles' )
            ->compile();
        $expected = 'SELECT @id FROM `articles`';

        $this->assertEquals( $expected, $actual );
    }

    public function testSelectTwo()
    {
        $obj = $this->_sphinxQl;

        $obj
            ->select( 'attr1' )
            ->from(
                array(
                     'index1'
                )
            );
        $actual = $obj->compile();

        $expected = 'SELECT `attr1` FROM `index1`';
        $this->assertEquals( $expected, $actual );
    }

    public function testSelectRow()
    {
        $query = $this->_sphinxQl;
        $actual = $query
            ->select( 'id' )
            ->from( 'articles' )
            ->compile();
        $expected = 'SELECT `id` FROM `articles`';

        $this->assertEquals( $expected, $actual );

        $query = $this->_sphinxQl;
        $actual = $query
            ->select( 'group' )
            ->from( 'articles' )
            ->compile();
        $expected = 'SELECT `group` FROM `articles`';

        $this->assertEquals( $expected, $actual );
    }

    public function testSelectWithFunc()
    {
        $obj = $this->_sphinxQl;

        $obj
            ->select( 'attr1', 'GEODIST( lat, lng, 0.745194, 0.407116 ) AS geodist' )
            ->from(
                array(
                     'index1'
                )
            );
        $actual = $obj->compile();

        $expected = 'SELECT `attr1`, GEODIST( lat, lng, 0.745194, 0.407116 ) AS geodist FROM `index1`';
        $this->assertEquals( $expected, $actual );

        $obj
            ->select( 'attr1', 'attr2 as mmm' )
            ->from(
                array(
                     'index1'
                )
            );
        $actual = $obj->compile();

        $expected = 'SELECT `attr1`, attr2 as mmm FROM `index1`';
        $this->assertEquals( $expected, $actual );
    }
}
