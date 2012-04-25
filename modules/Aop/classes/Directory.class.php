<?php
/**
 * UniPG
 * @package Tools
 */

/**
 * Класс для работы с дирректориями для хранения файлов классов, сгенерированных Aop.
 * Генерирует относительные пути для сгенерированных класов, аналогичные путям к файлу внутри проекта
 * @package Tools
 * @subpackage Tools_Aop
 */
class Aop_Directory
{
	/**
	 * Возвращает путь для хранения обработанного файла класса.
	 * Если в Aop_Main::$cache содержится путь к папке для новых файлов, то в ней сгенерируется путь=относительному пути к оригинальному файлу внутри проекта
	 * Если в Aop_Main::$cache=='', то сгенерированные файлы будут храниться в тех же папках, что и исходники
	 *
	 *
	 * @param string $path путь к оригинальному файлу класса
	 * @param string $cache Папка для хранения всех обработанных файлов классов. Должна существовать.
	 * @return string
	 */
	public static function getCacheDirName( $path, $cache )
	{
		// Папка для хранения всех обработанных файлов классов. Должна существовать.
		// Путь к исходному файлу класса внутри проекта. При необходимости весь создается в $cache
		$class_dir = dirname( $path );

		if ( '' != $cache )
		{
			$project_root = Miao_Path::getDefaultInstance()->getRoot();
			$class_dir = str_replace( $project_root, '', $class_dir );
		}
		$class_dir = ltrim( $class_dir, '\\/' );
		if ( $cache[ strlen( $cache ) - 1 ] != '/' )
		{
			$cache .= '/';
		}

		$class_dir = $class_dir . '/';

		self::_checkPath( $cache, $class_dir );

		$dir_name = $cache . $class_dir;

		return $dir_name;
	}

	/**
	 * Проверка текущего каталога на существование.
	 *
	 * @param string $base каталог для хранения сгенерированных файлов. Если не существует, генериться exception
	 * @param string $path относительный путь к файлу класса
	 * @return unknown
	 * @exception Aop_Exception
	 */
	protected static function _checkPath( $base, $path )
	{
		if ( !file_exists( $base ) )
		{
			throw new Aop_Exception( 'Кэш путь не существует, [ ' . $base . ' ]' );
		}

		$full_path = $base . $path;

		if ( !file_exists( $full_path ) )
		{
			Uniora_Tools_FileSystem_Directory::createPath( $full_path, 0777 );
		}

		return $path;
	}
}
