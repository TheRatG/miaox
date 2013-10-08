<?php
/**
 * @author vpak
 * @date 2012-09-03 12:16:55
 */
class Miaox_Exceptionizer_Catcher
{

	/**
	 *
	 * @var error level
	 */
	public $mask = E_ALL;

	/**
     *
     * @var boolean
     */
	public $ignoreOther = false;

	/**
     *
     * @var unknown_type
     */
	public $prevHdl = null;

	public function handler( $errno, $errstr, $errfile, $errline )
	{
		if ( !( $errno & error_reporting() ) )
		{
			return false;
		}
		if ( !( $errno & $this->mask ) )
		{
			if ( !$this->ignoreOther )
			{
				if ( $this->prevHdl )
				{
					$args = func_get_args();
					call_user_func_array( $this->prevHdl, $args );
				}
				else
				{
					return false;
				}
			}
			return true;
		}
		$types = array(
			"E_ERROR",
			"E_WARNING",
			"E_PARSE",
			"E_NOTICE",
			"E_CORE_ERROR",
			"E_CORE_WARNING",
			"E_COMPILE_ERROR",
			"E_COMPILE_WARNING",
			"E_USER_ERROR",
			"E_USER_WARNING",
			"E_USER_NOTICE",
			"E_STRICT",
			"E_RECOVERABLE_ERROR",
			"E_DEPRECATED",
			"E_USER_DEPRECATED" );

		$className = "E_EXCEPTION";
		foreach ( $types as $t )
		{
			$e = constant( $t );
			if ( $errno & $e )
			{
				$className = $t;
				break;
			}
		}
		throw new $className( $errstr, $errno, $errfile, $errline );
	}
}