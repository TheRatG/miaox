<?php
class Miaox_Compress_Client
{
	private $_jsPath = 'jslib';
	private $_cssPath = 'skin';
	private $_dstFolder;
	private $_enabled;

	/**
	 *
	 * @return the $_jsPath
	 */
	public function getJsPath()
	{
		return $this->_jsPath;
	}

	/**
	 *
	 * @param string $jsPath
	 */
	public function setJsPath( $jsPath )
	{
		$this->_jsPath = $jsPath;
	}

	/**
	 *
	 * @return the $_cssPath
	 */
	public function getCssPath()
	{
		return $this->_cssPath;
	}

	/**
	 *
	 * @param string $_cssPath
	 */
	public function setCssPath( $cssPath )
	{
		$this->_cssPath = $cssPath;
	}

	/**
	 *
	 * @return the $_dstFolder
	 */
	public function getDstFolder()
	{
		return $this->_dstFolder;
	}

	/**
	 *
	 * @param field_type $_dstFolder
	 */
	public function setDstFolder( $dstFolder )
	{
		$this->_dstFolder = $dstFolder;
	}

	/**
	 *
	 * @return the $_enabled
	 */
	public function getEnabled()
	{
		return $this->_enabled;
	}

	/**
	 *
	 * @param field_type $_enabled
	 */
	public function setEnabled( $enabled )
	{
		$this->_enabled = $enabled;
	}

	public function __construct( $dstFolder, $enabled = true )
	{
		$this->setDstFolder( $dstFolder );
		$this->setEnabled( $enabled );
	}

	public function getCss( array $filenameList, $compress = false )
	{
		$result = $this->_getFilenameList( $filenameList, $compress, '_minifyCss' );
		return $result;
	}

	public function getJs( array $filenameList, $compress = false )
	{
		$result = $this->_getFilenameList( $filenameList, $compress, '_minifyJs' );
		return $result;
	}

	public function prepareFileList( array $fileList )
	{
		$erMessage = array();
		$result = array();
		foreach ( $fileList as $filename )
		{
			$fullFilename = $filename;
			if ( DIRECTORY_SEPARATOR !== $filename[ 0 ] )
			{
				$fullFilename = $this->getDstFolder() . DIRECTORY_SEPARATOR . $filename;
				if ( !file_exists( $fullFilename ) )
				{
					$fullFilename = $this->getDstFolder() . DIRECTORY_SEPARATOR . $this->getCssPath() . DIRECTORY_SEPARATOR . $filename;
				}
			}

			if ( !file_exists( $fullFilename ) )
			{
				$erMessage[] = $fullFilename;
			}
			else
			{
				$result[] = $fullFilename;
			}
		}

		if ( !empty( $erMessage ) )
		{
			$erMessage = 'File list not found: ' . implode( ', ', $erMessage );
			throw new Miaox_Compress_Exception( $erMessage );
		}
		return $result;
	}

	protected function _getFilenameList( array $filenameList, $compress, $method )
	{
		$preparedFilenameList = $this->prepareFileList( $filenameList );
		if ( !$this->getEnabled() )
		{
			$result = $preparedFilenameList;
		}
		else
		{
			$dstFolder = $this->getDstFolder();
			$dstFilename = Miaox_Compress::makeFilename( $dstFolder, $filenameList );

			if ( $compress )
			{
				$result = $this->$method( $preparedFilenameList, $dstFilename );
			}
			else if ( file_exists( $dstFilename ) )
			{
				$result = array( $dstFilename );
			}
			else
			{
				$result = $preparedFilenameList;
			}
		}
		return $result;
	}

	protected function _minifyCss( $filenameList, $dstFilename )
	{
		$driver = new Miaox_Compress_Driver_Yui();
		$obj = new Miaox_Compress( $driver );
		$obj->minifyCss( $filenameList, $dstFilename );
		$result = array( $dstFilename );
		return $result;
	}

	protected function _minifyJs( $filenameList, $dstFilename )
	{
		$driver = new Miaox_Compress_Driver_ClosureCompiler();
		$obj = new Miaox_Compress( $driver );
		$obj->minifyJs( $filenameList, $dstFilename );
		$result = array( $dstFilename );

		return $result;
	}
}