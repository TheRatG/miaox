<?php
/**
 * SphinxQl.class.Test.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 19.03.13 11:59
 */

require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_Test extends Miaox_SphinxQl_Helper_Test
{
    public function testEmpty()
    {
        $this->assertTrue( true );
    }

    public function testExecuteMeta()
    {
        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles', 'articles_delta' )
            ->match( 'body', 'test' )
            ->where( 'is_valid', true )
            ->where( 'type', Miaox_SphinxQl::IN, array( 1, 2, 3 ) )
            ->orderBy( 'publish_date' )
            ->limit( 2, 2 );

        $meta = array();
        $search->execute( null, $meta );
        $expected = array(
            'total' => '1',
            'total_found' => '1',
            'time' => '0.000',
            'keyword[0]' => 't230',
            'docs[0]' => '3',
            'hits[0]' => '3',
        );
        $this->assertEquals( $expected, $meta );
    }
}
