<?php
$config = include dirname( __FILE__ ) . '/../data/config.php' ;
require_once PROJECT_MIAO_ROOT . '/modules/Autoload/classes/Autoload.class.php';

Miao_Autoload::register( $config['libs'] );
Miao_Path::register( $config );
Miao_Env::defaultRegister();
