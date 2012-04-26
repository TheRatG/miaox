<?php
/**
 * UniPG
 * @package Tools
 */

/**
 * Класс для работы с дирректориями для хранения файлов классов, сгенерированных
 * Miaox_Aop.
 * Генерирует относительные пути для сгенерированных класов, аналогичные путям к
 * файлу внутри проекта
 *
 * @package Tools
 * @subpackage Tools_Aop
 */
class Miaox_Aop_File
{
	/**
	 *
	 *
	 *
	 *
	 * Возвращает путь для хранения обработанного файла класса.
	 * Папка для хранения всех обработанных файлов классов. Должна существовать.
	 *
	 * @param string $filePath
	 *        	путь к оригинальному файлу класса
	 * @param string $cache
	 *        	Папка для хранения всех обработанных файлов классов. Должна
	 *        	существовать.
	 * @return string
	 */
	public static function getCacheDirName( $filePath, $cache )
	{
		self::checkDir( $cache );

		$basename = basename( $filePath, 'php' );
		$basename = basename( $basename, 'class' );

		$ar = explode( '_', $basename );
		array_pop( $ar );

		$subdir = '';
		if ( !empty( $ar ) )
		{
			$subdir = DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $ar );
		}

		$result = $cache . $subdir;

		if ( !file_exists( $result ) )
		{
			mkdir( $result );
		}

		return $result;
	}

	public static function getCompiledClassFile( $filePath )
	{
		$compiledClassFile = basename( $filePath, "php" ) . md5( $filePath ) . ".php";
		return $compiledClassFile;
	}

	/**
	 * Проверка текущего каталога на существование.
	 *
	 * @param string $base
	 *        	каталог для хранения сгенерированных файлов. Если не
	 *        	существует, генериться exception
	 * @param string $path
	 *        	относительный путь к файлу класса
	 * @return unknown @exception Miaox_Aop_Exception
	 */
	public static function checkDir( $dir )
	{
		if ( !file_exists( $dir ) || !is_dir( $dir ) || !is_writeable( $dir ) )
		{
			throw new Miaox_Aop_Exception( sprintf( 'Invalid path (%s): dir doesn\'t exists or not writeable', $dir ) );
		}
	}
}
