<?php
/**
 * config.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 19.03.13 17:47
*/

define( 'MODULE_ROOT', realpath( __DIR__ . '/..' ) );
define( 'SEARCHD_HOST', '127.0.0.1' );
define( 'SEARCHD_PORT', 4499 );

define( 'BIN_SEARCHD', 'searchd' );
define( 'BIN_INDEXER', 'indexer' );

require_once MODULE_ROOT . '/../classes/SphinxQl.class.php';
