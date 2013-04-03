<?php
/**
 * Queue.class.Test.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 03.04.13 11:56
*/
require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_Queue_Test extends Miaox_SphinxQl_Helper_Test
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

    public function testExecuteMeta()
    {
        $search = $this->_sphinxQl;
        $search->select()
            ->from( 'articles', 'articles_delta' )
            ->match( 'body', 'test' )
            ->where( 'is_valid', 1 )
            ->where( 'type', Miaox_SphinxQl::IN, array( 1, 2, 3 ) )
            ->orderBy( 'publish_date' )
            ->limit( 2, 2 );

        $meta = array();
        $result = $search->execute( null, $meta );

        // --- dump ---
        echo __FILE__ . __METHOD__ . chr( 10 );
        var_dump( $result ) . chr( 10 );
        // --- // ---
    }
}
