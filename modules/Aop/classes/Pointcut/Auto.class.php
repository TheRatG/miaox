<?php
/**
 * UniPG
 * @package Tools
 * @subpackage Tools_Aop
 */

/**
 * @package Tools
 * @subpackage Tools_Aop
 *
 */
class Miaox_Aop_Pointcut_Auto extends Miaox_Aop_Pointcut
{
	/**
	 * Список значений из набора before|after|around
	 *
	 * @var array
	 */
	protected  $_auto;


	/**
	 * Конструктор
	 *
	 * @param Aop_Advice $action
	 * @param string|array $class список классов, включаемых в срез
	 * @param string|array $function список функций/методов, включаемых в срез
	 * @param string|array $auto тип auto pointcuta
	 * @param string|array $nclass список классов, НЕ включаемых в срез
	 * @param string|array $nfunction список функций/методов, НЕ включаемых в срез
	 */
	public function __construct( $action, $class, $function, $auto, $nclass, $nfunction )
	{
		parent::__construct( $action, $class, $function, $nclass, $nfunction );

		// Defining Auto( s )
		$this->_auto = ( is_array( $auto ) ? $auto : split( ",[ ]*", $auto ) );
	}


	/**
	 * Getter для $this->_auto
	 *
	 * @return array
	 */
	public function getAuto()
	{
		return $this->_auto;
	}

	/**
	 * Проверяет на наличие $v в $this->_auto
	 *
	 * @param string $v
	 * @return boolean
	 */
	public function hasAuto( $v )
	{
		return in_array( $v, $this->_auto, true );
	}
}
