<?php
interface Miaox_Acs_Adapter_Interface
{
	/**
	 *
	 * @param scalar $groupId
	 * @param scalar $resourceId
	 * @return bool
	 */
	public function allowResource( $groupId, $resourceId );

	/**
	 *
	 * @param scalar $groupId
	 * @param scalar $resourceId
	 * @return bool
	 */
	public function denyResource( $groupId, $resourceId );

	public function getPermission();

	public function getUser();
}