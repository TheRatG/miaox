<?php
/**
 * @author vpak
 * @date 2013-01-22 09:45:15
 */
class Miaox_Search_SphinxQl
{
	public function __construct()
	{

	}

	public function select()
	{
		$query = new Miaox_Search_SphinxQl_Query( $this );
		$result = call_user_func_array( array(
			$query,
			'select' ), func_get_args() );
		return $result;
	}
}