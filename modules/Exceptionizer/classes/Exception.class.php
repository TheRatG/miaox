<?php
class Miaox_Exceptionizer_Exception extends Exception
{

	public function __construct( $str = null, $no = 0, $file = null, $line = 0 )
	{
		parent::__construct( $str, $no );

		$this->file = $file;
		$this->line = $line;
	}
}