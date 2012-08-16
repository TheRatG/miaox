<?php
class Miaox_Acs_Adapter_Db implements Miaox_Acs_Adapter_Interface
{

	/**
	 *
	 * @var Miao_log
	 */
	private $_log = null;

	/**
	 *
	 * @var Miaox_Acs_Adapter_Db_Group
	 */
	private $_group;
	/**
	 *
	 * @var Miaox_Acs_Adapter_Db_People
	 */
	private $_people;
	/**
	 *
	 * @var Miaox_Acs_Adapter_Db_Resource
	 */
	private $_resource;
	/**
	 *
	 * @var Miaox_Acs_Adapter_Db_Permission
	 */
	private $_permission;

	/**
	 *
	 * @param Miaox_PDO $pdo
	 * @param Miao_Log $log
	 */
	public function __construct( Miaox_PDO $pdo, Miao_Log $log = null )
	{
		if ( !$log )
		{
			$this->_log = Miao_Log::easyFactory( '' );
		}
		else
		{
			$this->_log = $log;
		}
		$this->_pdo = $pdo;
		$this->_pdo->setLog( $log );
		$this->_init();
	}

	public function allowResource()
	{
	}

	public function denyResource()
	{
	}

	public function getPermission()
	{
	}

	public function getUser()
	{
	}

	/**
	 *
	 * @return Miaox_Acs_Adapter_Db_Group
	 */
	public function group()
	{
		return $this->_group;
	}

	/**
	 *
	 * @return Miaox_Acs_Adapter_Db_People
	 */
	public function people()
	{
		return $this->_people;
	}

	/**
	 *
	 * @return Miaox_Acs_Adapter_Db_Resource
	 */
	public function resource()
	{
		return $this->_resource;
	}

	/**
	 *
	 * @return Miaox_Acs_Adapter_Db_Permission
	 */
	public function permission()
	{
		return $this->_permission;
	}

	protected function _init()
	{
		$this->_permission = new Miaox_Acs_Adapter_Db_Permission( $this->_pdo );
		$this->_group = new Miaox_Acs_Adapter_Db_Group( $this->_pdo );
		$this->_people = new Miaox_Acs_Adapter_Db_People( $this->_pdo );
		$this->_resource = new Miaox_Acs_Adapter_Db_Resource( $this->_pdo );
	}
}