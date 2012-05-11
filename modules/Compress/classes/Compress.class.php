<?php
class Miaox_Compress
{
	private $_driver;

	public function __construct( $driver )
	{
		$this->setDriver( $driver );
	}

	/**
	 * @return the $_driver
	 */
	public function getDriver()
	{
		return $this->_driver;
	}

	/**
	 * @param field_type $_driver
	 */
	public function setDriver( $driver )
	{
		if ( !$driver instanceof Miaox_Compress_Driver_Interface )
		{
			$msg = sprintf(
					'Invalid object %s, must be implement Miaox_Compress_Driver_Interface interface',
					get_class( $driver ) );

			throw new Miaox_Compress_Exception( $msg );
		}

		$this->_driver = $driver;
	}

	public function minifyJs( $jsList, $dstFilename )
	{
		$options = array();
		$options[ 'v' ] = true;
		$options[ 'type' ] = 'js';
		$result = $this->_driver->minify( $jsList, $dstFilename, $options );
		return $result;
	}

	public function minifyCss( $cssList, $dstFilename )
	{
		$options = array();
		$options[ 'v' ] = true;
		$options[ 'type' ] = 'css';
		$result = $this->_driver->minify( $cssList, $dstFilename, $options );
		return $result;
	}

	/**
	 *
	 *
	 * @param string $dstFolder
	 * @param array $filenameList
	 */
	static public function makeFilename( $dstFolder, $filenameList )
	{
		$type = explode( '.', $filenameList[ 0 ] );
		$type = '.' . array_pop( $type );
		$result = implode( ':', $filenameList );
		$result = $dstFolder . '/' . md5( $result ) . $type;
		return $result;
	}
}