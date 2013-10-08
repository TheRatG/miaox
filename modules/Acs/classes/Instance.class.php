<?php
/**
 *
 */
class Miaox_Acs_Instance
{

	/**
	 *
	 * @var Miaox_Acs_Instance
	 */
	protected static $_instance;

	/**
	 *
	 * @var array
	 */
	protected $_config = array();

	/**
	 *
	 * @return Miaox_Acs_Instance
	 */
	static protected function _getInstance()
	{
		if ( !self::$_instance )
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	static public function log( array $config = null )
	{
		$self = self::_getInstance();
		if ( empty(  $config ) )
		{
			$config = $self->getConfig();
		}
		$config = $config[ 'log' ];
		$filename = $config[ 'filename' ];
		$verbose = $config[ 'verbose' ];
		$log = Miao_Log::easyFactory( $filename, $verbose );
		return $log;
	}

	static public function pdo( array $config = null )
	{
		$self = self::_getInstance();
		if ( empty(  $config ) )
		{
			$config = $self->getConfig();
		}
		$config = $config[ 'pdo' ];
		$result = $self->_pdo( $config );
		return $result;
	}

	static public function adapter( array $config = null )
	{
		if ( empty(  $config ) )
		{
			$self = self::_getInstance();
			$config = $self->getConfig();
		}

		assert( array_key_exists( 'adapterClassName', $config ) );

		$adapterClassName = $config[ 'adapterClassName' ];
		$pdo = self::pdo( $config );
		$log = self::log(  $config  );
		$result = new $adapterClassName( $pdo, $log );
		if ( !$result instanceof Miaox_Acs_Adapter_Interface )
		{
			$message = sprintf( 'Adapter class (%s) must be implement of Miaox_Acs_Adapter_Interface', $adapterClassName );
			throw new Miaox_Acs_Adapter_Exception( $message );
		}

		return $result;
	}

	protected function _pdo( array $config )
	{
		assert( array_key_exists( 'dsn', $config ) );
		assert( array_key_exists( 'username', $config ) );
		assert( array_key_exists( 'password', $config ) );

		if ( isset( $config[ 'logFilename' ] ) )
		{
			$verbose = isset( $config[ 'logVerbose' ] ) ? $config[ 'logVerbose' ] : false;
			$log = Miao_Log::easyFactory( $config[ 'logFilename' ], $verbose );
		}

		$dsn = $config[ 'dsn' ];
		$user = $config[ 'username' ];
		$pass = $config[ 'password' ];
		$options = isset( $config[ 'options' ] ) ? $options : array();

		$pdo = new Miaox_PDO( $dsn, $user, $pass, $options );
		$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$pdo->setLog( $log );
		return $pdo;
	}

	public function getConfig()
	{
		$self = self::_getInstance();
		if ( !$self->_config )
		{
			$configObj = Miao_Config::Libs( 'Miaox_Acs', true );
			$result = $configObj->toArray();
			if ( empty( $result ) )
			{
				$result = self::getDefaultConfig();
			}
			$self->_config = $result;
		}
		return $self->_config;
	}

	public function getDefaultConfig()
	{
		$dir = dirname( __FILE__ );
		$dir = Miao_Config::Main()->get( 'paths.shared' ) . '/acs';
		$dbFilename = $dir . '/db.sqlite';
		$logFilename = Miao_Config::Main()->get( 'paths.logs' ) . '/acs_log';
		$result = array(
			'adapterClassName' => 'Miaox_Acs_Adapter_Db',
			'log' => array(
				'enabled' => true,
				'filename' => $logFilename,
				'verbose' => false ),
			'pdo' => array(
				'dsn' => 'sqlite:' . $dbFilename,
				'username' => '',
				'password' => '',
				'options' => array() ) );
		return $result;
	}
}