<?php
class Miaox_Compress_Driver
{
	/**
	 *
	 * @var Miao_Log
	 */
	protected $_log;

	/**
	 * @return Maio_Log
	 */
	public function getLog()
	{
		return $this->_log;
	}

	public function __construct( $log )
	{
		$this->_log = $log;
	}

	public function mergeFiles( array $files, $outpuFilename )
	{
		assert( is_array( $files ) && !empty( $files ) );

		$message = sprintf( 'MINIFY: start mergeFiles to "%s"', $outpuFilename );
		$this->_log->info( $message );

		if ( file_exists( $outpuFilename ) )
		{
			unlink( $outpuFilename );
		}

		$shell = $this->_getShell();
		foreach ( $files as $filename )
		{
			if ( !file_exists( $filename ) || !is_readable( $filename ) )
			{
				$message = sprintf( 'MINIFY ERROR: File not found "%s"', $filename );
				$this->_log->crit( $message );
				throw new Miaox_Compress_Driver_Exception( $message );
			}

			$command = sprintf( 'cat "%s" >> "%s"', $filename, $outpuFilename );
			$returnVal = 0;
			$shell->exec( $command, $returnVal, false );
		}

		$message = sprintf( 'MINIFY: end mergeFiles' );
		$this->_log->info( $message );

		return $outpuFilename;
	}

	/**
	 *
	 * @return Miao_Tools_Shell
	 */
	protected function _getShell()
	{
		$result = Miao_Tools_Shell::getInstance( true );
		$result->setLog( $this->getLog() );
		return $result;
	}
}