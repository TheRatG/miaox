<?php
/**
 * UniPg
 * @package Tools
 */

/**
 * @package Tools
 * @subpackage Tools_Aop
 *
 * <code>
 *  $aop_obj = new Aop_Client();
 * 	$class_path = $aop_obj->getPathByClassName( 'Realty_Import_MultiThread' );
 *  $project_root = Uniora_Core_Config::Main()->paths->root;
 * 	$aop_obj->requireFile( $class_path, $project_root . '/data/import/statistic.xml' );
 * </code>
 *
 */
class Miaox_Aop_Client
{
	/**
	 * compact mode on/off
	 *
	 * @var boolean
	 */
	private $_compact = true;
	/**
	 * Перекомпилировать все классы, даже если ничего не изменилось. Только для отладки.
	 *
	 * @var boolean
	 */
	private $_recompile = false;
	/**
	 * Папка для хранения компилированных классов. Если "", то храняться в тех же дирректориях где исходники
	 *
	 * @var string
	 */
	private $_cache = "";

	/**
	 * Кодировка результата
	 *
	 * @var string
	 */
	private $_encoding = "utf-8";


	/**
	 * Конструктор
	 */
	public function __construct( $config = null )
	{
		$this->_init( $config );
	}

	/**
	 * The compiled class codes directory
	 *
	 * @param string $dir
	 * @exception Miaox_Aop_Exception
	 */
	public function setCacheDirectory( $dir = '' )
	{
		if ( !file_exists( $dir )
			|| !is_dir( $dir )
			|| !is_writeable( $dir )
		)
		{
			throw new Miaox_Aop_Exception(
				sprintf( 'Указанный путь неверен или недоступен для записи: %s', $dir )
			);
		}
		$this->_cache = $dir;
	}

	/**
	 * Возвращает директорию куда складывает преобразованные файлы классов
	 *
	 * @return string
	 */

	public function getCacheDirectory()
	{
		return $this->_cache;
	}

	/**
	 * Setter для флага обязательной перекомпиляции всех классов
	 *
	 * @param boolean $flag
	 */
	public function setRecompile( $flag )
	{
		$this->_recompile = ( boolean )$flag;
	}

	/**
	 * Getter для флага обязательной перекомпиляции всех классов
	 *
	 * @return boolean
	 */
	public function getRecompile()
	{
		return $this->_recompile;
	}

	/**
	 * Устанавливает нужную кодировку результата
	 *
	 * @param string $encoding
	 */
	public function setEncoding( $encoding )
	{
		$this->_encoding = strtolower( $encoding );
	}

	/**
	 * compact mode on/off
	 *
	 * @param boolean $flag
	 */
	public function setCompact( $flag )
	{
		$this->_compact = ( boolean )$flag;
	}

	/**
	 * Получить путь к файлу по его имени
	 *
	 * TODO maybe del
	 *
	 * @param string $class_name
	 * @return string
	 */
	public function getPathByClassName( $class_name )
	{
		$class_path = Miao_Autoload::getFilenameByClassName( $class_name );
		return $class_path;
	}


	/**
	 * Подключение исходного/преобразованного файла в зависимости от $this->_$recompile и наличия изменений в файлах классов, аспектов
	 *
	 * @param string $filePath путь к исходному классу
	 * @param string|Miaox_Aop_Weave|Uniora_Tools_Aop_Aspect|array $weave можно использовать: имя xml файла, папку с xml файлами (добавяться все файлы xml вместе с поддиректориями), Uniora_Tools_Aop_Aspect, массив из предыдущих вариантов, Uniora_Tools_Aop_Weave
	 */

	public function requireFile( $filePath, $weave = "./" )
	{
		$filename = $this->getCompiledFilename( $filePath, $weave );
		require_once( $filename );
		return $filename;
	}

	/**
	 * Получение имени файла в зависимости от $this->_$recompile и наличия изменений в файлах классов, аспектов
	 *
	 * @param string $filePath путь к исходному классу
	 * @param string|Miaox_Aop_Weave|Uniora_Tools_Aop_Aspect|array $weave можно использовать: имя xml файла, папку с xml файлами (добавяться все файлы xml вместе с поддиректориями), Uniora_Tools_Aop_Aspect, массив из предыдущих вариантов, Uniora_Tools_Aop_Weave
	 * @return string
	 */
	public function getCompiledFilename( $filePath, $weave = "./" )
	{
		// Check if file to be compiled exists
		if ( !file_exists( $filePath ) )
		{
	       	throw new Miaox_Aop_Exception( "[ Aspect Error ]: File " . $filePath . " does not exist!" );
		}

		// Correcting aspects typing
		if ( !( $weave instanceof Miaox_Aop_Weave ) )
		{
			$weave = new Miaox_Aop_Weave( $weave );
		}

		$dir = Miaox_Aop_Directory::getCacheDirName( $filePath, $this->_cache );

		// Retrieving information
		// Defining Last Modified Date
		$lastModified = filemtime( $filePath );

		if ( $lastModified < $weave->getLastModified() )
		{
			$lastModified = $weave->getLastModified();
		}

		// Compiled Class File - ".php" added to enable correctly Server File Type Handler
	    $compiledClassFile = basename( $filePath, "php" ) . md5( $filePath ) . ".php";

		// Checking if is necessary to compile file
		// First condition: No aspects defined
		// Second condition: Compiled file does not exist
		// Third condition: Compiled file is older than one ( or more ) of the aspects
		// Fourth condition: Programmer defined to recompile
		if ( !$weave->hasAspects() ||
			!file_exists( $dir . $compiledClassFile ) ||
	        filemtime( $dir . $compiledClassFile ) <= $lastModified ||
			$this->_recompile
		)
		{
	    	$this->_compile( $filePath, $dir . $compiledClassFile, $weave );
		}

		// Check if the compiled file exists
		if ( file_exists( $dir . $compiledClassFile ) )
		{
	    	// Load the compiled file
			return $dir . $compiledClassFile;
		}
		else if ( $weave->hasAspects() === false )
		{
			// Load original file ( no aspects defined )
			return $filePath;
		}
		else
		{
			throw new Miaox_Aop_Exception(
				"[ Aspect Error ]: Compiled File " . $dir . $compiledClassFile . " [ From Original: " . $filePath . " ] could not be loaded!" );
		}
	}

	/**
	 * Преобразование файла класса, если необходимо
	 *
	 * @param string $filePath имя исходного файла с классом
	 * @param string имя результирующего файла с классом $compiledFilePath
	 * @param Miaox_Aop_Weave $weave
	 */
	protected function _compile( $filePath, $compiledFilePath, $weave )
	{
		// Get file content
		$fContent = $this->_preProcessing( file( $filePath ) );

		$compiler = new Miaox_Aop_CodeCompiler( $fContent, $weave );
		$compiler->compile( $this->_compact );

		// Saving compiled file
		if ( !$fp = fopen( $compiledFilePath, "w" ) )
		{
			throw new Miaox_Aop_Exception(
				"[ Aspect Error ]: File " . $compiledFilePath . " [ From Original: " . $filePath . " ] could not be created/loaded for write!" );
		}

		$code = $compiler->getCode();

		$code = $this->_iconv( $code, 'out' );

		if ( !fwrite( $fp, $code ) )
		{
        	throw new Miaox_Aop_Exception(
				"[ Aspect Error ]: Could not write compiled data in file " . $compiledFilePath . " [ From Original: " . $filePath . " ]!"
			);
		}

		fclose( $fp );
		chmod( $compiledFilePath, 0666 );
	}

	/**
	 * Препроцессинг:
	 * trim,
	 * удаляет многострочные комментарии,
	 * если в файле не было -- добавляет "\n?>" в конец файла
	 *
	 * @param string $code
	 * @return string
	 */
	protected function _preProcessing ( $code )
	{
		$code = preg_replace( array( '/\s+$/', '/\/\*.+?\*\//s' ), '', ( implode( "", $code ) ) );
		if ( !preg_match( '/\?\>$/', $code ) )
		{
			$code .= "\n".' ?>';
		}

		$code = $this->_iconv( $code, 'in' );

		return $code;
	}

	/**
	 * Перекодировка
	 *
	 * @param string $code исходный код
	 * @param string $dir 'in'|'out' направление перекодировки 'in' в 'utf-8'; 'out' из 'utf-8'
	 * @return string
	 */
	protected function _iconv( $code, $dir )
	{
		if ( $this->_encoding != 'utf-8' )
		{
			if ( 'in' == $dir )
			{
				$code = iconv( $this->_encoding, 'utf-8', $code );
			}
			else if ( 'out' == $dir )
			{
			 	$code = iconv( 'utf-8', $this->_encoding, $code );
			}
		}
		return $code;
	}

		/**
	 * Инициализирует объект
	 */
	private function _init( $config )
	{
		$configFilename = dirname( __FILE__ ) . '/../data/config.php';
		if ( file_exists( $configFilename ) )
		{
			$config = include $configFilename;
			if ( isset( $config[ 'cache_dir' ] ) )
			{
				$dir = $config[ 'cache_dir' ];
				if ( !file_exists( $dir ) || !is_dir( $dir ) || !is_writeable( $dir ) )
				{
					throw new Exception( sprintf( 'Invalid path %s or not writeable', $dir ) );
				}
				$this->setCacheDirectory( $dir );
			}
			if ( isset( $config[ 'recompile' ] ) )
			{
				$this->setRecompile( ( boolean ) $config[ 'recompile' ] );
			}
			if ( isset( $config[ 'encoding' ] ) && !empty( $config[ 'encoding' ] ) )
			{
				$this->setEncoding( $config[ 'encoding' ] );
			}
			if ( isset( $config[ 'compact' ] ) )
			{
				$this->setCompact( ( boolean ) $config[ 'compact' ] );
			}
		}
	}
}