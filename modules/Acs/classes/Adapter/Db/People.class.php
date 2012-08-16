<?php
class Miaox_Acs_Adapter_Db_People extends Miaox_PDO_Table
{
	protected function _init()
	{
		$this->setName( 'tbl_acs_people' );
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
		$query = 'CREATE  TABLE  IF NOT EXISTS "'.$this->getName().'" ("id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL , "uid" VARCHAR NOT NULL  UNIQUE , "group_id" INTEGER NOT NULL )';
		$this->_pdo->exec( $query );
	}

	public function insert( $uid, $groupId )
	{
		$query = 'INSERT INTO "tbl_acs_people" ( uid, group_id ) VALUES( :uid, :group_id )';
		$statement = $this->_pdo->prepare( $query );
		$statement->bindValue( ':uid', $uid );
		$statement->bindValue( ':group_id', $groupId );
		$statement->execute();
	}
}