<?php
/**
 * @author vpak
 * @date 2012-08-28 15:31:57
 */
class Miaox_File_Upload
{

	protected $_baseDir;

	protected $_mode;

	/**
	 *
	 * @param string $baseDir Base dir for saving files
	 */
	public function __construct( $baseDir, $mode = 0777 )
	{
		$this->_baseDir = $baseDir;
		$this->_mode = $mode;
	}

	/**
	 *
	 * @param array $files $_FILES
	 * @return array
	 */
	public function runByFiles( $files )
	{
		$result = array();
		foreach ( $files as $index => $file )
		{
			$filename = $file[ 'tmp_name' ];
			$result[ $index ] = $this->run( $filename );
		}
		return $result;
	}

	public function run( $file )
	{
		$newFilename = $this->getFilename( $file );
		$dirname = dirname( $newFilename );
		if ( !file_exists( $dirname ) )
		{
			Miaox_File::mkdir( $dirname, $this->_mode );
		}
		$res = copy( $file, $newFilename );
		return $newFilename;
	}

	public function getFilename( $file )
	{
		$hash = Miaox_File::hash( $file );
		$ext = Miaox_File::getExtension( $file );
		$addDir = $this->getAddDirByHash( $hash );

		$result = array();
		$result[] = $this->_baseDir . $addDir;
		$result[] = $hash . '.' . $ext;
		$result = implode( DIRECTORY_SEPARATOR, $result );
		return $result;
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