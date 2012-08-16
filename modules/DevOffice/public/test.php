<?php
require realpath( dirname( __FILE__ ) . '/../../../' ) . '/scripts/bootstrap.php';

$groupId = 2;
$resourceId = 1;

$acs = Miaox_Acs_Instance::acs();
$result = $acs->isAllow($groupId, $resourceId);

 // --- dump ---
echo '<pre>';
echo __FILE__ . chr( 10 );
echo __METHOD__ . chr( 10 );
var_dump( $result );
echo '</pre>';
// --- // --