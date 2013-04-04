<?php
/**
 * Limit.class.Test.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 04.04.13 10:20
 */
require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_Limit_Test extends Miaox_SphinxQl_Helper_Test
{
    public function testLimitOne()
    {
        $expected = 3;
        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->where( 'id', Miaox_SphinxQl::IN, array( 1, 2, 3, 4 ) )
            ->limit( $expected );
        $actual = $search->execute();
        $this->assertEquals( $expected, count( $actual ) );
    }

    public function testLimitTwo()
    {

        $search = $this->_sphinxQl;
        $search
            ->select( 'id' )
            ->from( 'articles' )
            ->where( 'id', Miaox_SphinxQl::IN, array( 1, 2, 3, 4 ) )
            ->limit( 1, 1 );

        $expected = array( array( 'id' => 2 ) );
        $actual = $search->execute();
        $this->assertEquals( $expected, $actual );
    }
}
