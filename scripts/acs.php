<?php

function includeBootstrap( $bootstrap )
{
	if ( empty( $bootstrap ) )
	{
		require_once __DIR__ . '/bootstrap.php';
	}
	else if ( file_exists( $bootstrap ) && is_readable( $bootstrap ) )
	{
		require_once $bootstrap;
	}
	else
	{
		$msg = sprintf( 'Invalid param --bootstrap, file (%s) not found or is not readable', $bootstrap );
		throw new Exception( $msg );
	}
}

try
{
	$bootstrap = '';
	$opts = getopt( '', array( 'bootstrap:' ) );
	extract( $opts );
	includeBootstrap( $bootstrap );

	$log = Miao_Log::easyFactory( '', true );
	$message = 'Run script: ' . __FILE__;
	$log->debug( $message );
	$message = sprintf( 'Params: %s', print_r( $opts, true ) );
	$log->debug( $message );

	$config = Miaox_Acs_Instance::getConfig();
	$config[ 'log' ]['verbose'] = true;
	$adapter = Miaox_Acs_Instance::adapter( $config );
	$builder = new Miaox_Acs_Adapter_Db_Builder( $adapter );
	$builder->run();
}
catch ( Exception $ex )
{
	echo "\n";
	echo "Error!\n";
	echo $ex->getMessage();
	echo "\n\n";
}