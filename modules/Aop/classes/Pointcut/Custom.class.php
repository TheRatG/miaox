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
class Miaox_Aop_Pointcut_Custom extends Miaox_Aop_Pointcut
{
	/**
	 * Список имен срезов
	 *
	 * @var array
	 */
	protected $_name;

	/**
	 * Конструктор
	 *
	 * @param Miaox_Aop_Advice $action
	 * @param string|array $class список классов, включаемых в срез
	 * @param string|array $function список функций/методов, включаемых в срез
	 * @param string|array $name имя среза
	 * @param string|array $nclass список классов, НЕ включаемых в срез
	 * @param string|array $nfunction список функций/методов, НЕ включаемых в срез
	 */
	public function __construct( $action, $class, $function, $name, $nclass, $nfunction )
	{
		parent::__construct( $action, $class, $function, $nclass, $nfunction );

		// Defining Name( s )
		$this->_name = $this->_extractAr( $name );
	}

	/**
	 * getter для $this->_name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Проверяет есть ли $v в $this->_name
	 *
	 * @param string $v
	 * @return boolean
	 */
	public function hasName( $v )
	{
		return in_array( $v, $this->_name, true );
	}
}
