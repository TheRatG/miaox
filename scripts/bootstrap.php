<?php
$config = include dirname( __FILE__ ) . '/../data/config_map.php';
foreach ( $config[ 'libs' ] as $value )
{
	if ( 'Miao' == $value[ 'name' ] )
	{
		require_once $value[ 'path' ] . '/modules/Autoload/classes/Autoload.class.php';
		break;
	}
}

Miao_Autoload::register( $config[ 'libs' ] );
Miao_Path::register( $config );
Miao_Env::defaultRegister();
