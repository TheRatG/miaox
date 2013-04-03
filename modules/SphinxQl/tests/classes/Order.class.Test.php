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

    public function testOrderAsc()
    {
        $search = $this->_sphinxQl;
        $search
            ->select( 'type' )
            ->from( 'articles' )
            ->where( 'id', Miaox_SphinxQl::IN, array( 1, 2, 3 ) )
            ->orderBy( 'type', Miaox_SphinxQl::ORDER_DESC );
        $actual = $search->execute();

        // --- dump ---
        echo __FILE__ . __METHOD__ . chr( 10 );
        var_dump( $actual ) . chr( 10 );
        // --- // ---
    }
}
