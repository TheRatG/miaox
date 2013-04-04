<?php
/**
 * Suite.class.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 03.04.13 17:33
 */

require_once 'Connection.class.Test.php';
require_once 'Select.class.Test.php';
require_once 'Queue.class.Test.php';
require_once 'Match.class.Test.php';
require_once 'Where.class.Test.php';
require_once 'Order.class.Test.php';
require_once 'Limit.class.Test.php';
require_once 'Option.class.Test.php';
require_once 'Snippet.class.Test.php';
require_once 'SphinxQl.class.Test.php';

class Miaox_SphinxQl_Suite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite( 'PHPUnit SphinxQl' );

        $suite->addTestSuite( 'Miaox_SphinxQl_Match_Test' );
        //$suite->addTestSuite( 'Miaox_SphinxQl_Connection_Test' );
        $suite->addTestSuite( 'Miaox_SphinxQl_Select_Test' );

        $suite->addTestSuite( 'Miaox_SphinxQl_Where_Test' );
        $suite->addTestSuite( 'Miaox_SphinxQl_Order_Test' );
        $suite->addTestSuite( 'Miaox_SphinxQl_Limit_Test' );
        $suite->addTestSuite( 'Miaox_SphinxQl_Option_Test' );
        $suite->addTestSuite( 'Miaox_SphinxQl_Snippet_Test' );
        $suite->addTestSuite( 'Miaox_SphinxQl_Queue_Test' );
        $suite->addTestSuite( 'Miaox_SphinxQl_Test' );
        return $suite;
    }
}
