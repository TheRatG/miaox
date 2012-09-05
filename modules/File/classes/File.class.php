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
		$result = self::getExtensionByMime( $filename );
		if ( !$result )
		{
			$result = pathinfo( $filename, PATHINFO_EXTENSION );
		}
		return $result;
	}

	static public function getExtensionByMime( $filename )
	{

		$map = array(
			'image/bmp' => 'bmp',
			'image/cis-cod' => 'cod',
			'image/gif' => 'gif',
			'image/ief' => 'ief',
			'image/jpeg' => 'jpg',
			'image/pipeg' => 'jfif',
			'image/tiff' => 'tif',
			'image/x-cmu-raster' => 'ras',
			'image/x-cmx' => 'cmx',
			'image/x-icon' => 'ico',
			'image/x-portable-anymap' => 'pnm',
			'image/x-portable-bitmap' => 'pbm',
			'image/x-portable-graymap' => 'pgm',
			'image/x-portable-pixmap' => 'ppm',
			'image/x-rgb' => 'rgb',
			'image/x-xbitmap' => 'xbm',
			'image/x-xpixmap' => 'xpm',
			'image/x-xwindowdump' => 'xwd',
			'image/png' => 'png',
			'image/x-jps' => 'jps',
			'image/x-freehand' => 'fh',
			'plain/text' => 'txt' );
		$mimetype = '';

		$exceptionizer = new Miaox_Exceptionizer( E_ALL );
		try
		{
			if ( !function_exists( 'mime_content_type' ) )
			{

				function mime_content_type( $filename )
				{
					$finfo = finfo_open( FILEINFO_MIME );
					$mimetype = finfo_file( $finfo, $filename );
					finfo_close( $finfo );
				}
			}
			$mimetype = mime_content_type( $filename );
		}
		catch ( E_WARNING $e )
		{

		}
		unset( $exceptionizer );

		$result = null;
		if ( isset( $map[ $mimetype ] ) )
		{
			$result = $map[ $mimetype ];
		}
		return $result;
	}

	/**
	 * Recursive delete dir
	 * @param unknown_type $dir
	 */
	static public function rmdirr( $dir )
	{
		if ( is_dir( $dir ) )
		{
			$objects = scandir( $dir );
			foreach ( $objects as $object )
			{
				if ( $object != "." && $object != ".." )
				{
					if ( filetype( $dir . "/" . $object ) == "dir" )
						self::rmdirr( $dir . "/" . $object );
					else
						unlink( $dir . "/" . $object );
				}
			}
			reset( $objects );
			rmdir( $dir );
		}
	}

	/**
	 * Рекурсивно создает путь. Пытается применить $mode ко всем создаваемым папкам.
	 *
	 * @param string $path путь
	 * @param int $mode
	 * @return string $path -- в случае успешного создания пути, '' в противоположном случае
	 */
	static public function mkdir( $dir, $mode = 0777, $exception = false )
	{
		$res = $dir;
		if ( !file_exists( $dir ) )
		{
			$exceptionizer = null;
			if ( true == $exception )
			{
				$exceptionizer = new Miaox_Exceptionizer( E_ALL );
			}

			try
			{
				$flag = true;
				$dirs = self::parsePath( $dir );
				foreach ( $dirs as $dir )
				{
					if ( mkdir( $dir, $mode ) !== true )
					{
						$flag = false;
						break;
					}
					if ( chmod( $dir, $mode ) !== true )
					{
						$flag = false;
						break;
					}
				}
			}
			catch ( Miaox_Exceptionizer_Exception $e )
			{
				if ( !$e instanceof E_NOTICE )
				{
					$message = sprintf( '(%s) %s', $dir, $e->getMessage() );
					throw new Miaox_File_Exception( $message );
				}
			}

			if ( $exceptionizer )
			{
				unset( $exceptionizer );
			}
			if ( !$flag )
			{
				$res = '';
			}
		}
		return $res;
	}

	/**
	 * Разбор строки с путем к файлу. Возвращает массив с папками.
	 *
	 * @param string $path исходня строка с путем
	 * @param bool $check_exist возвращать только несуществующие папки
	 * @param bool $reverse массив в обратном порядке -- для удобного создания пути
	 * @return array
	 */
	static public function parsePath( $path, $check_exist = true, $reverse = true )
	{
		$path = trim( str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, $path ) );
		$res = array();
		if ( !empty( $path ) )
		{
			$file_exists = false;
			while ( $path )
			{
				if ( $check_exist )
				{
					$file_exists = file_exists( $path );
					if ( $file_exists )
					{
						break;
					}
				}
				$res[] = self::correctPath( $path, false );
				$path = dirname( $path );
				if ( empty( $path ) || $path == DIRECTORY_SEPARATOR || $path == '.' )
				{
					break;
				}
			}
		}
		if ( $reverse )
		{
			$res = array_reverse( $res );
		}
		return $res;
	}

	/**
	 * Trim строки с путем, опционально проверяет на существование. Если адрес надо проверять и он не существует -- возвращает пустую строку.
	 *
	 * @param string $path путь
	 * @param boolean $check_if_exist надо ли проверять путь на существование
	 * @return string
	 * @exception Miaox_File_Exception
	 */
	static public function correctPath( $path, $check_if_exist = true )
	{
		if ( !is_string( $path ) )
		{
			throw new Miaox_File_Exception( 'path не является строкой', __METHOD__, array(
				'path' => $path ) );
		}
		$path = rtrim( trim( $path ), '\\/ ' );

		if ( $check_if_exist && !file_exists( $path ) )
		{
			$path = '';
		}
		return $path;
	}
}