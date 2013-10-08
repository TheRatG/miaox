<?php
class Miaox_Acs_Adapter_Db_Builder
{
	/**
	 *
	 * @var Miaox_Acs_Adapter_Db
	 */
	private $_adapter;

	public function __construct( Miaox_Acs_Adapter_Db $adapter )
	{
		$this->_adapter = $adapter;
	}

	public function run()
	{
		$this->buildGroup();
		$this->buildPeople();
		$this->buildResource();
		$this->buildPermisssion();
	}

	public function buildGroup()
	{
		$table = $this->_adapter->group();
		$table->drop();
		$table->create();
		$table->insert( 'root' );
		$table->insert( 'guest' );
	}

	public function buildPeople()
	{
		$table = $this->_adapter->people();
		$table->drop();
		$table->create();
		$table->insert( 'root', 1 );
		$table->insert( 'guest', 2 );
	}

	public function buildResource()
	{
		$table = $this->_adapter->resource();
		$table->drop();
		$table->create();
		$obj = new Miaox_Acs_Resource();
		$data = $obj->getList();
		if ( !empty( $data ) )
		{
			foreach ( $data as $item )
			{
				$table->insert( $item );
			}
		}
	}

	public function buildPermisssion()
	{
		$table = $this->_adapter->permission();
		$table->drop();
		$table->create();
		$table->allowForAll( 1 );
	}
}