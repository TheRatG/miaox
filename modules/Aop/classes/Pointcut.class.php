<?php
/**
 * UniPG
 * @package Tools
 */

/**
 * Класс описывает срез
 *
 * @package Tools
 * @subpackage Tools_Aop
 */
class Miaox_Aop_Pointcut
{
	/**
	 * Список классов, включаемых в срез
	 *
	 * @var array
	 */
	protected $_className;
	/**
	 * Список функций/методов, включаемых в срез
	 *
	 * @var array
	 */
	protected $_functionName;
	/**
	 * Список классов, НЕ включаемых в срез
	 *
	 * @var array
	 */
	protected $_notInClassName;
	/**
	 * Список функций/методов, НЕ включаемых в срез
	 *
	 * @var array
	 */
	protected $_notInFunctionName;
	/**
	 * Методы, применяемые к срезу
	 *
	 * @var Miaox_Aop_Advice
	 */
	protected $_advice;

	/**
	 *
	 * @param Miaox_Aop_Advice $advice
	 * @param string|array $class
	 *        	список классов, включаемых в срез
	 * @param string|array $function
	 *        	список функций/методов, включаемых в срез
	 * @param string|array $nclass
	 *        	список классов, НЕ включаемых в срез
	 * @param string|array $nfunction
	 *        	список функций/методов, НЕ включаемых в срез
	 */
	public function __construct( Miaox_Aop_Advice $advice, $class, $function, $nclass, $nfunction )
	{
		// Defining Class( es )
		$this->_className = $this->_extractAr( $class );

		// Defining Not In Class( es )
		$this->_notInClassName = $this->_extractAr( $nclass );

		// Defining Function( s )
		$this->_functionName = $this->_extractAr( $function );

		// Defining Not In Function( s )
		$this->_notInFunctionName = $this->_extractAr( $nfunction );

		// Remove start/end carriage return chars in the code
		$this->_advice = $advice;
	}

	/**
	 * getter $this->_className
	 *
	 * @return array
	 */
	public function getClassName()
	{
		return $this->_className;
	}

	/**
	 *
	 * @param string $v
	 * @return boolean
	 */
	public function hasClassName( $v )
	{
		return in_array( $v, $this->_className, true );
	}

	/**
	 * getter $this->_functionName
	 *
	 * @return array
	 */
	public function getFunctionName()
	{
		return $this->_functionName;
	}

	/**
	 *
	 * @param string $v
	 * @return boolean
	 */
	public function hasFunctionName( $v )
	{
		return in_array( $v, $this->_functionName, true );
	}

	/**
	 *
	 * @return array
	 */
	public function getNotInClassName()
	{
		return $this->_notInClassName;
	}

	/**
	 *
	 * @param string $v
	 * @return boolean
	 */
	public function hasNotInClassName( $v )
	{
		return in_array( $v, $this->_notInClassName, true );
	}

	/**
	 *
	 * @return array
	 */
	public function getNotInFunctionName()
	{
		return $this->_notInFunctionName;
	}

	/**
	 *
	 * @param string $v
	 * @return boolean
	 */
	public function hasNotInFunctionName( $v )
	{
		return in_array( $v, $this->_notInFunctionName, true );
	}

	/**
	 * Возвращает advice для данного среза
	 *
	 * @return Miaox_Aop_Advice
	 */
	public function &getAdvice()
	{
		return $this->_advice;
	}

	protected function _extractAr( $string )
	{
		$result = $string;
		if ( !is_array( $string ) )
		{
			$result = explode( ',', $string );
			$result = array_map( 'trim', $result );
		}
		return $result;
	}
}
