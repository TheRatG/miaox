<?php
require_once __DIR__ . '/../scripts/config.php';
require_once 'Searchd.class.Test.php';
/**
 * Class Miaox_SphinxQl_Helper_Test
 */
class Miaox_SphinxQl_Helper_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Miaox_SphinxQl_Helper_Test
     */
    static private $_searchd;

    static function setUpBeforeClass()
    {
        self::$_searchd = new Miaox_SphinxQl_Searchd_Test( MODULE_ROOT, SEARCHD_HOST, SEARCHD_PORT, BIN_SEARCHD, BIN_INDEXER );
        self::$_searchd->start();
    }

    static public function tearDownAfterClass()
    {
        self::$_searchd->stop();
    }
}
