<?php
/**
 * @author vpak
 * @date 2013-01-22 17:28:05
 */
class Miaox_SphinxQl_Query_Expression
{
	/**
	 * The expression content
	 *
	 * @var  string
	 */
	protected $string;

	/**
	 * The constructor accepts the expression as string
	 *
	 * @param  string  $string  The content to prevent being quoted
	 */
	public function __construct($string = '')
	{
		$this->string = $string;
	}

	/**
	 * Return the unmodified expression
	 *
	 * @return  string  The unaltered content of the expression
	 */
	public function value()
	{
		return (string) $this->string;
	}

	/**
	 * Returns the unmodified expression
	 *
	 * @return  string  The unaltered content of the expression
	 */
	public function __toString()
	{
		return (string) $this->value();
	}
}