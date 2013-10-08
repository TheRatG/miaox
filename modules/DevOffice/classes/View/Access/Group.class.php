<?php
/**
 * @author vpak
 * @date 2012-08-07 11:28:21
 */
class Miaox_DevOffice_View_Access_Group extends Miaox_DevOffice_View
{

	/**
	 * Init view bloks and template params for layout
	 */
	public function _initializeBlock()
	{
		parent::_initializeBlock();

		$adapter = Miaox_Acs_Instance::adapter();
		$permission = $adapter->permission()->getList();
		$group = $adapter->group()->getList();

		$this->setTmplVars( 'permission', $permission );
		$this->setTmplVars( 'group', $group );
		$this->setTmplVars( 'bodyClass', 'access_group' );
	}
}