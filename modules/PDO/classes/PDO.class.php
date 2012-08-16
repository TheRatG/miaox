<?php
class Miaox_PDO extends PDO
{
	protected $_queryCount = 0;

	protected $_execTime = 0;

	private $_loggerCallback = NULL;

	public function __construct( $dsn, $username = null, $password = null, $driver_options = array() )
	{
		$this->_dsn = $dsn;
		parent::__construct( $dsn, $username, $password, $driver_options );
		$this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		if ( !$this->getAttribute( PDO::ATTR_PERSISTENT ) )
		{
			$this->setAttribute( PDO::ATTR_STATEMENT_CLASS, array(
				'Miaox_PDO_Statement',
				array( $this ) ) );
		}
	}

	public function setLog( $log )
	{
		$this->_log = $log;
	}

	public function getLog()
	{
		if ( empty( $this->_log ) )
		{
			$logFilename = Miao_Config::Main()->get( 'paths.logs' ) . '/pdo_log';
			$this->_log = Miao_Log::easyFactory( $logFilename );
		}
		return $this->_log;
	}

	public function increment_query_count()
	{
		$this->_queryCount++;
	}

	public function get_query_count()
	{
		return $this->_queryCount;
	}

	public function add_exec_time( $time )
	{
		$this->_execTime += $time;
	}

	public function get_exec_time_ms()
	{
		return $this->_execTime;
	}

	public function log( $args, $title, $priority = Miao_Log::DEBUG )
	{
		if ( is_array( $args ) )
		{
			if ( count( $args ) == 1 )
			{
				$args = array_shift( $args );
			}
			else
			{
				$args = var_export( $args, true );
			}
		}

		$message = $title . ': ' . $args;
		$this->getLog()->log( $message, $priority );
	}

	public function exec( $sql )
	{
		$this->log( $sql, 'Query (PDO->exec())' );
		$this->increment_query_count();

		$start = microtime( true );
		$return = parent::exec( $sql );
		$finish = microtime( true );
		$this->add_exec_time( $finish - $start );
		$this->log( $this->get_exec_time_ms(), 'Time' );
		return $return;
	}

	public function query()
	{
		$this->increment_query_count();
		$args = func_get_args();
		$this->log( $args, 'Query (PDO->query())' );

		$start = microtime( true );
		$return = call_user_func_array( array( $this, 'parent::query' ), $args );
		$finish = microtime( true );
		$this->add_exec_time( $finish - $start );
		$this->log( $this->get_exec_time_ms(), 'Time' );
		return $return;
	}
}