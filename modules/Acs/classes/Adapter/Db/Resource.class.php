<?php
class Miaox_Acs_Adapter_Db_Resource extends Miaox_PDO_Table
{
	protected function _init()
	{
		$this->setName( 'tbl_acs_resource' );
	}

	public function getList()
	{
		$query = 'SELECT * FROM ' . $this->getName();
		$statement = $this->_pdo->query( $query );
		$result = $statement->fetchAll();
		return $result;
	}

	public function create()
	{
		$query = 'CREATE  TABLE  IF NOT EXISTS "'.$this->getName().'" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , "name" VARCHAR NOT NULL  UNIQUE )';
		$this->_pdo->exec( $query );
	}

	public function insert( $name )
	{
		$query = 'INSERT INTO "'.$this->getName().'" ( "name" ) VALUES( :name )';
		$statement = $this->_pdo->prepare( $query );

		$statement->bindValue( ':name', $name );
		$statement->execute();
	}
}