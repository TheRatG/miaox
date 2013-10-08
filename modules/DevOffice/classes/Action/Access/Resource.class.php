<?php
class Miaox_DevOffice_Action_Access_Resource extends Miao_Office_Action
{

	public function execute()
	{
		$error = '';
		$res = false;
		$message = '';
		try
		{
			$request = Miao_Office_Request::getInstance();
			$groupId = $request->getValueOf( 'group_id' );
			$resourceId = $request->getValueOf( 'resource_id' );
			$action = $request->getValueOf( 'action' );

			$acs = Miaox_Acs_Instance::adapter();

			$class = 'icon-off';
			if ( $action )
			{
				$class = 'icon-ok';

				$res = $acs->allowResource( $groupId, $resourceId );
				$message .= 'Success allow. ';
			}
			else
			{
				$res = $acs->denyResource( $groupId, $resourceId );
				$message .= 'Success deny. ';
			}
			if ( false === $res )
			{
				$error = 'Something wrong';
			}
			$message .= sprintf( 'Group id: %s, resource_id: %s, action: %s', $groupId, $resourceId, $action );
		}
		catch ( Exception $e )
		{
			$error = $e->getMessage();
		}
		$data = array(
			'message' => $message,
			'error' => $error,
			'class' => $class );
		return json_encode( $data );
	}
}