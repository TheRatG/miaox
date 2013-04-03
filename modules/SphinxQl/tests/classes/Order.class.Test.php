<?php
/**
 * Order.class.Test.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 03.04.13 12:00
 */
require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_Order_Test extends Miaox_SphinxQl_Helper_Test
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

    public function testOrderDirection()
    {
        $search = $this->_sphinxQl;
        $search
            ->select( 'type' )
            ->from( 'articles' )
            ->where( 'id', Miaox_SphinxQl::IN, array( 1, 4, 5 ) )
            ->orderBy( 'type' );
        $actual = $search->execute();
        $expected = array(
            0 => array(
                'type' => '1',
            ),
            1 => array(
                'type' => '2',
            ),
            2 => array(
                'type' => '3',
            ),
        );
        $this->assertEquals( $expected, $actual );

        $search
            ->select( 'type' )
            ->from( 'articles' )
            ->where( 'id', Miaox_SphinxQl::IN, array( 1, 4, 5 ) )
            ->orderBy( 'type', Miaox_SphinxQl::ORDER_DESC );
        $actual = $search->execute();
        $expected = array_reverse( $expected );
        $this->assertEquals( $expected, $actual );
    }

    public function testOrderSeveralFields()
    {
        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->where( 'id', Miaox_SphinxQl::IN, array( 1, 2, 4, 5 ) )
            ->orderBy( 'type' )
            ->orderBy( 'publish_date', Miaox_SphinxQl::ORDER_DESC );
        $actual = $search->execute();

        $expected = array(
            0 => array(
                'id' => '2',
                'publish_date' => '1363845000',
                'type' => '1',
            ),
            1 => array(
                'id' => '1',
                'publish_date' => '1132223498',
                'type' => '1',
            ),
            2 => array(
                'id' => '4',
                'publish_date' => '1364017800',
                'type' => '2',
            ),
            3 => array(
                'id' => '5',
                'publish_date' => '1364107800',
                'type' => '3',
            ),
        );
        $this->assertEquals( $expected, $actual );
    }
}
