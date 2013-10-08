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

    public function testLimitThree()
    {
        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->offset( 5 );
        $actual = $search->compile();
        $expected = 'SELECT * FROM `articles` LIMIT 5, ' . PHP_INT_MAX;
        $this->assertEquals( $expected, $actual );

        $search
            ->select()
            ->from( 'articles' )
            ->limit( 5 )
            ->offset( 5 );
        $actual = $search->compile();
        $expected = 'SELECT * FROM `articles` LIMIT 5, 5';
        $this->assertEquals( $expected, $actual );
    }

    public function testLimitFive()
    {
        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->limit( 0, 5 );
        $actual = $search->compile();
        $expected = 'SELECT * FROM `articles` LIMIT 0, 5';
        $this->assertEquals( $expected, $actual );
    }

    public function testLimitWithMaxMatches()
    {
        $maxMatches = 1000;
        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->offset( 5 )
            ->option( Miaox_SphinxQl_Query_Select::OPTION_MAX_MATCHES, $maxMatches );
        $actual = $search->compile();
        $expected = sprintf( 'SELECT * FROM `articles` LIMIT 5, %d OPTION max_matches=%d', $maxMatches, $maxMatches );
        $this->assertEquals( $expected, $actual );
    }
}
