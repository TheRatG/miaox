<?php
/**
 * UniPG
 * @package Tools
 * @subpackage Tools_Aop
 */

/**
 * Advice -- методы, выполняющиеся для срезов ( pointcuts )
 *
 * @package Tools
 * @subpackage Tools_Aop
 */
class Miaox_Aop_Advice
{
	/**
	 * массив, каждый элемент -- код для исполнения
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Конструктор
	 *
	 */
	public function __construct()
	{
		$this->_data = array();
	}

	/**
	 * Добавить исполняемый код в массив $data
	 *
	 * @param string $code
	 */
	public function addData( $code )
	{
		$this->_data[ count( $this->_data ) ] = ltrim(
			rtrim( $code, "\r\n\x0B\t" ), "\r\n\x0B"
		);
	}

	/**
	 * Возвращает элемент $data с индексом $i или все данные, если $i не указано
	 *
	 * @param int|null $i
	 * @return str
	 */
	public function getData( $i = null )
	{
		if ( $i !== null && array_key_exists( $i, $this->_data ) )
		{
			return $this->_data[ $i ];
		}

		return implode( "", $this->_data );
	}
}
