<?php
abstract class Miaox_PDO_Table
{

	/**
	 *
	 * @var Miaox_PDO
	 */
	protected $_pdo;

	/**
	 *
	 * @var string
	 */
	protected $_tableName;

	/**
	 * @return the $tableName
	 */
	public function getName()
	{
		return $this->_tableName;
	}

	/**
	 * @param string $tableName
	 */
	public function setName( $tableName )
	{
		$this->_tableName = $tableName;
	}

	public function __construct( $pdo )
	{
		$this->_pdo = $pdo;
		$this->_init();
	}

	public function drop()
	{
		$query = 'DROP TABLE  IF EXISTS "' . $this->getName() . '"';
		$this->_pdo->exec( $query );
	}

	abstract protected function _init();
}