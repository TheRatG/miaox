<?php
interface Miaox_Acs_Adapter_Interface
{
	/**
	 *
	 * @param scalar $groupId
	 * @param scalar $resourceId
	 * @return bool
	 */
	public function allowResource( $groupId, $resourceName );

	/**
	 *
	 * @param scalar $groupId
	 * @param scalar $resourceName
	 * @return bool
	 */
	public function denyResource( $groupId, $resourceName );
}