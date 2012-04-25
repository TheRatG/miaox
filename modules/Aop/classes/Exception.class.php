<?php
/**
 * UniPG
 * @package Tools
 */

/**
 * @package Tools
 * @subpackage Tools_Aop
 *
 */
class Miaox_Aop_Exception extends Exception
{
	/**
	 * @param string $message
	 */
	public function __construct( $message )
	{
		parent::__construct( $message );
	}
}
