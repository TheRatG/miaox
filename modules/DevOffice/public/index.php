<?php
require realpath( dirname( __FILE__ ) . '/../../../' ) . '/scripts/bootstrap.php';
try
{
	$request = Miao_Office_Request::getInstance();
	$params = $_GET;

	$factory = new Miao_Office_Factory( array( 'defaultPrefix' => 'Miaox_DevOffice' ) );
	$fo = $factory->getOffice( $params, array( '_view' => 'Main' ) );
	$fo->sendResponse();
}
catch ( Exception $e )
{
	 // --- dump ---
	echo '<pre>';
	echo __FILE__ . chr( 10 );
	echo __METHOD__ . chr( 10 );
	var_dump( $e );
	echo '</pre>';
	// --- // ---
}