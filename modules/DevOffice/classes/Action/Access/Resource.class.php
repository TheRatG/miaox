<?php
class Miaox_DevOffice_Action_Access_Resource extends Miao_Office_Action
{

	public function execute()
	{
		$error = '';
		try
		{
			$request = Miao_Office_Request::getInstance();
			$groupId = $request->getValueOf( 'group_id' );
			$resourceId = $request->getValueOf( 'resource_id' );
			$action = $request->getValueOf( 'action' );

			$acs = Miaox_Acs_Instance::acs();

			$class = 'icon-off';
			if ( $action )
			{
				$class = 'icon-ok';

				$acs->allowResource($groupId, $resourceId);
			}
			else
			{
				$acs->denyResource($groupId, $resourceId);
			}

			$message = sprintf( 'Group id: %s, resource_id: %s, action: %s', $groupId, $resourceId, $action );
		}
		catch ( Exception $e )
		{
			$error = $e->getMessage();
		}

		$data = array( 'message' => $message, 'error' => $error, 'class' => $class );
		return json_encode( $data );
	}
}