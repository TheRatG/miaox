<?php
/**
 * Option.class.Test.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 04.04.13 12:20
*/
require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_Option_Test extends Miaox_SphinxQl_Helper_Test
{
    public function testMaxMatches()
    {
        $maxMatches = 6000;
        $search = $this->_sphinxQl;
        $search->setGlobalOption( Miaox_SphinxQl_Query_Select::OPTION_MAX_MATCHES, $maxMatches );
        $search
            ->select()
            ->from( 'articles' );

        $actual = $search
            ->compile();
        $expected = sprintf( 'SELECT * FROM `articles` OPTION max_matches=%d', $maxMatches );

        $this->assertEquals( $expected, $actual );
    }
}
