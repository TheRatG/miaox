<?php
/**
 * @author vpak
 * @date 2012-08-28 15:31:57
 */
class Miaox_File_Upload
{

	protected $_baseDir;

	/**
	 *
	 * @param string $baseDir Base dir for saving files
	 */
	public function __construct( $baseDir )
	{
	}

	public function run( $file )
	{
	}

	public function getFilename( $file )
	{
		$result = '';
		$hash = md5( $file . filesize( $file ) . filetype( $file ) );
	}

	public function getAddDirByHash( $hash, $cntSubDir = 2, $cntChar = 2 )
	{
		$hash = trim( $hash );
		if ( !is_numeric( $hash ) )
		{
			$hash = Miaox_File::convert( $hash );
		}

		//add zero
		$len = strlen( $hash );
		$len = $len - $cntSubDir * $cntChar;
		if ( $len < 0 )
		{
			$hash = str_repeat( '0', $len * ( -1 ) ) . $hash;
		}

		$ar = str_split( $hash, $cntChar );
		$ar = array_slice( $ar, count( $ar ) - $cntSubDir, $cntSubDir );
		$result = DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $ar );
		return $result;
	}
}