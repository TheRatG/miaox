<?php
class Realty_Compress_Driver_Yui extends Realty_Compress_Driver implements Realty_Compress_Driver_Interface
{
	const TYPE_JS = 'js';
	const TYPE_CSS = 'css';

	private $_options = array();
	private $_jarFilename = array();

	private $_optionsGlobal = array(
		'type' => '<js|css> Specifies the type of the input file',
		'charset' => '<charset> Read the input file using <charset>',
		'line-break' => ' <column> Insert a line break after the specified column number',
		'v' => 'Display informational messages and warnings',
		'verbose' => 'Display informational messages and warnings' );

	private $_optionsJs = array(
		'nomunge' => '                 Minify only, do not obfuscate',
		'preserve-semi' => '           Preserve all semicolons',
		'disable-optimizations' => '   Disable all micro optimizations' );

	/**
	 *
	 *
	 * @param unknown_type $jarFilename
	 * @param array $options
	 */
	public function __construct( $jarFilename = '', Realty_Tools_Log $log = null )
	{
		if ( empty( $jarFilename ) )
		{
			$jarFilename = Realty_Config::Libs( __CLASS__ )->get( 'jar_filename' );
		}
		if ( is_null( $log ) )
		{
			$log = Uniora_Tools_Log::getInstance();
		}
		parent::__construct( $log );
		$this->setJarFilename( $jarFilename );
	}

	public function setJarFilename( $filename )
	{
		$message = '';
		if ( empty( $filename ) )
		{
			$message = 'Invalid param $filename: must be not empty';
		}
		if ( !file_exists( $filename ) )
		{
			$message = sprintf( 'File (%s) not found', $filename );
		}
		$pos = mb_stripos( $filename, '.jar' );
		if ( false === $pos )
		{
			$message = sprintf( 'Invalid file (%s) must be .jar', $filename );
		}

		if ( !empty( $message ) )
		{
			throw new Realty_Compress_Driver_Yui_Exception( $message );
		}

		$this->_jarFilename = $filename;
	}

	public function getJarFilename()
	{
		return $this->_jarFilename;
	}


	public function setOptions( array $options )
	{
		$errors = array();
		$res = $this->checkOptions( $options, $errors );
		if ( !$res )
		{
			$message = 'Invalid options';
			foreach ( $errors as $error )
			{
				$message .= sprintf( "\n %s", $error );
			}

			throw new Realty_Compress_Driver_Yui_Exception( $message );
		}

		if ( array_key_exists( 'v', $options ) )
		{
			$options[ 'verbose' ] = true;
			unset( $options[ 'v' ] );
		}

		$this->_options = array_merge( $this->_options, $options );
	}

	public function getOptions()
	{
		return $this->_options;
	}
	
	public function addOption($key, $value)
	{
	   $this->_options[$key] = $value;
	}

	public function checkOptions( array $options, array &$errors = array() )
	{
		$result = true;

		if ( !array_key_exists( 'type', $options ) )
		{
			$errors[] = 'Option "type" is mandatory';
			$result = false;
		}
		else
		{
			$type = $options[ 'type' ];
			if ( $type != self::TYPE_CSS && $type != self::TYPE_JS )
			{
				$errors[] = 'Option "type" can be set to "css" or "js" ';
				$result = false;
			}

			$optionsMap = $this->_optionsGlobal;
			if ( self::TYPE_JS == $type )
			{
				$optionsMap = array_merge_recursive( $optionsMap, $this->_optionsJs );
			}
			$diff = array_diff_key( $options, $optionsMap );
			if ( !empty( $diff ) )
			{
				$error = 'Detected invalid options:';
				foreach ( $diff as $item )
				{
					$error .= sprintf( ' "%s"', $item );
				}
				$errors[] = $error;

				$result = false;
			}
		}

		return $result;
	}

	public function minify( $fileList, $dstFilename, array $options = array() )
	{
		if ( !empty( $options ) )
		{
			$this->setOptions( $options );
		}

		$type = $options[ 'type' ];
		$totalFilename = realpath( dirname( $dstFilename ) ) . '/' . 'total_' . rand( 1, 900 ) . '.' . $type;
		$totalFilename = $this->mergeFiles( $fileList, $totalFilename );

		$command = $this->_makeMinifyCommand( $totalFilename, $dstFilename );

		$shell = $this->_getShell();
		$returnVal = 0;
		$output = null;
		$shell->exec( $command, $returnVal, false, $output );

		unlink( $totalFilename );

		if ( !empty( $output ) && is_array( $output ) )
		{
			$output = $this->_clearErrors( $output );
		}

		return $output;
	}

	protected function _clearErrors( array $errors )
	{
		$result = array();
		foreach ( $errors as $error )
		{
			if ( empty( $error ) )
			{
				continue;
			}
			if ( false !== strpos( $error, '[INFO]' ) )
			{
				continue;
			}
			$result[] = $error;
		}
		return $result;
	}

	private function _makeMinifyCommand( $totalFilename, $outputFilename )
	{
		$command = sprintf( 'java -Xms56M -Xmx128M -jar "%s"', $this->getJarFilename() );

		$command .= sprintf( ' "%s"', $totalFilename );
		$command .= sprintf( ' -o "%s"', $outputFilename );

		foreach ( $this->_options as $option => $value )
		{
			if ( is_bool( $value ) || empty( $value ) )
			{
				$command .= sprintf( ' --%s', $option );
			}
			else
			{
				$command .= sprintf( ' --%s=%s', $option, $value );
			}
		}

		return $command;
	}
}