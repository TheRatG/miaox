<?php
/**
 * Queue.class.Test.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 03.04.13 11:56
 */
require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_Queue_Test extends Miaox_SphinxQl_Helper_Test
{
    public function testEnQueue()
    {
        $search = $this->_sphinxQl;
        $search
            ->select( 'id' )
            ->from( 'articles' )
            ->where( 'id', Miaox_SphinxQl::IN, array( 1 ) )
            ->enqueue();
        $search
            ->select( 'id' )
            ->from( 'articles' )
            ->where( 'id', Miaox_SphinxQl::IN, array( 2 ) )
            ->enqueue();

        $actual = $search->executeBatch();
        $expected = array(
            0 => array(
                0 => array(
                    'id' => '1',
                ),
            ),
            1 => array(
                0 => array(
                    'id' => '2',
                ),
            ),
        );
        $this->assertEquals( $expected, $actual );
    }
}
