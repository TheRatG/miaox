<?php
class Miaox_PDO_Statement extends PDOStatement
{
	/**
	 *
	 * @var Miaox_PDO
	 */
	protected $_pdo;

	/**
	 *
	 * @var array
	 */
	protected $_params;

	protected static $_typeMap = array(
		PDO::PARAM_BOOL => "PDO::PARAM_BOOL",
		PDO::PARAM_INT => "PDO::PARAM_INT",
		PDO::PARAM_STR => "PDO::PARAM_STR",
		PDO::PARAM_LOB => "PDO::PARAM_LOB",
		PDO::PARAM_NULL => "PDO::PARAM_NULL" );

	protected function __construct( Miaox_PDO $pdo )
	{
		$this->_pdo = $pdo;
	}

	/**
	 * TODO: change log, make full query.
	 * (non-PHPdoc)
	 * @see PDOStatement::execute()
	 */
	public function execute( $inputParameters = null )
	{
		$this->_pdo->log( $this->queryString, 'Query (PDOStatement->execute())' );
		if ( !empty( $this->_params ) )
		{
			$this->_pdo->log( var_export( $this->_params, true ) , 'Parameters' );
			$this->_params = array();
		}
		if ( !empty( $inputParameters ) )
		{
			$this->_pdo->log( var_export( $inputParameters, true ) , 'Parameters' );
		}
		$this->_pdo->increment_query_count();

		$start = microtime( true );
		$return = parent::execute( $inputParameters );
		$finish = microtime( true );
		$this->_pdo->add_exec_time( $finish - $start );
		$this->_pdo->log( $this->_pdo->get_exec_time_ms(), 'Time' );
		return $return;
	}

	public function bindValue( $pos, $value, $type = PDO::PARAM_STR )
	{
		$type_name = isset( self::$_typeMap[ $type ] ) ? self::$_typeMap[ $type ] : 'PDO::PARAM_STR';
		$this->_params[] = array( $pos, $value, $type_name );
		$result = parent::bindValue( $pos, $value, $type );
		return $result;
	}
}