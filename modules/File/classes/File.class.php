<?php
class Miaox_File
{

	static public function convert( $string )
	{
		$result = base_convert( $string, 36, 10 );
		return $result;
	}

	static public function hash( $filename )
	{
		clearstatcache();
		$result = md5( $filename . filesize( $filename ) . filetype( $filename ) );
		return $result;
	}

	static public function getExtension( $filename )
	{
		$result = pathinfo( $filename, PATHINFO_EXTENSION );
		return $result;
	}
}