<?php
class Miaox_Acs_Adapter_Db_Permission extends Miaox_PDO_Table
{

	protected function _init()
	{
		$this->setName( 'tbl_acs_permission' );
	}

	public function getList()
	{
		$query = array();
		$query[] = 'SELECT';
		$query[] = 'r.id as resource_id';
		$query[] = ', r.name as resource_name';
		$query[] = ', g.id as group_id';
		$query[] = ', g.name as group_name';
		$query[] = ', CASE WHEN p.group_id IS NOT NULL THEN 1 ELSE 0 END as is_allow';
		$query[] = 'FROM "tbl_acs_resource" as r, "tbl_acs_group" as g';
		$query[] = 'LEFT JOIN "' . $this->getName() . '" as p ON p.resource_id = r.id and p.group_id = g.id';

		$statement = $this->_pdo->query( implode( ' ', $query ) );
		$data = $statement->fetchAll();

		$result = array();
		foreach ( $data as $item )
		{
			$key = $item[ 'resource_id' ];
			if ( !isset( $result[ $key ] ) )
			{
				$result[ $key ] = array(
					'resource_id' => $item[ 'resource_id' ],
					'resource_name' => $item[ 'resource_name' ],
					'groups' => array() );
			}
			$result[ $key ][ 'groups' ][] = array(
				'group_id' => $item[ 'group_id' ],
				'group_name' => $item[ 'group_name' ],
				'is_allow' => $item[ 'is_allow' ] );
		}
		return $result;
	}

	public function create()
	{
		$query = 'CREATE  TABLE  IF NOT EXISTS "' . $this->getName() . '" ( "group_id" INTEGER NOT NULL , "resource_id" INTEGER NOT NULL , PRIMARY KEY ( "group_id", "resource_id" ) )';
		$this->_pdo->exec( $query );
	}

	/**
	 *
	 * @param integer $groupId
	 * @param integer $resourceId
	 * @return boolean
	 */
	public function allowResource( $groupId, $resourceId )
	{
		$query = 'INSERT INTO "' . $this->getName() . '" ( "group_id", "resource_id" ) VALUES( :group_id, :resource_id )';
		$statement = $this->_pdo->prepare( $query );
		$statement->bindValue( ':group_id', $groupId, PDO::PARAM_INT );
		$statement->bindValue( ':resource_id', $resourceId, PDO::PARAM_INT );
		$result = $statement->execute();
		return $result;
	}

	public function denyResource( $groupId, $resourceId )
	{
		$query = 'DELETE FROM "' . $this->getName() . '" WHERE group_id = :group_id and resource_id = :resource_id';
		$statement = $this->_pdo->prepare( $query );
		$statement->bindValue( ':group_id', $groupId );
		$statement->bindValue( ':resource_id', $resourceId );
		$result = $statement->execute();
		return $result;
	}

	public function allowForAll( $groupId )
	{
		$query = 'INSERT INTO tbl_acs_permission ( "group_id", "resource_id" ) SELECT :group_id, id FROM tbl_acs_resource';
		$statement = $this->_pdo->prepare( $query );
		$statement->bindValue( ':group_id', $groupId );
		$res = $statement->execute();
		return $res;
	}
}