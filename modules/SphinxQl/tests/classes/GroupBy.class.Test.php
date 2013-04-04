<?php
/**
 * GroupBy.class.Test.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 04.04.13 18:20
 */
require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_GroupBy_Test extends Miaox_SphinxQl_Helper_Test
{
    public function testGroupByOneField()
    {
        $search = $this->_sphinxQl;
        $search
            ->select( 'type', 'COUNT(*) as cnt' )
            ->from( 'articles' )
            ->groupBy( 'type' )
            ->orderBy( 'type' );
        $actual = $search->execute();
        $expected = array(
            0 => array(
                'type' => '1',
                'cnt' => '3',
            ),
            1 => array(
                'type' => '2',
                'cnt' => '1',
            ),
            2 => array(
                'type' => '3',
                'cnt' => '1',
            ),
            3 => array(
                'type' => '4',
                'cnt' => '2',
            ),
        );
        $this->assertEquals( $expected, $actual );
    }
}
