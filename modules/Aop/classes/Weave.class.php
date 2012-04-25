<?php
/**
 * UniPG
 * @package Tools
 */

/**
 *  Содержит и оперирует набором загруженных аспектов
 * @package Tools
 * @subpackage Tools_Aop
 */
class Miaox_Aop_Weave
{
	/**
	 * Флаг ленивой инициализации
	 *
	 * @var bool
	 */
	protected  $_initialized;
	/**
	 * Самое последнее изменение файлов аспектов
	 *
	 * @var int
	 */
	protected $_lastModified;
	/**
	 * Массив Aop_Aspect
	 *
	 * @var array
	 */
	protected $_aspects;
	/**
	 *Список дирректорий
	 *
	 * @var array
	 */
	protected $_directories;

	/**
	 * Конструктор
	 *
	 * @return Aop_Weave
	 */
	public function __construct()
	{
		$this->_aspects = array();
		$this->_directories = array();

		$this->_initialized = false;
		$this->_lastModified = 0;

		// Loading Aspects
		$numArgs = func_num_args();

		for ( $i = 0; $i < $numArgs; $i++)
		{
			$arg = func_get_arg( $i );
			$this->_handleLoad( $arg );
		}
	}


	/**
	 * @param Aop_Aspect $aspect
	 * @return boolean
	 */
	public function addAspect( &$aspect )
	{
		if ( $aspect instanceof Aop_Aspect )
		{
			$this->_aspects[ count( $this->_aspects ) ] = & $aspect;

			// Analising the most recent last modified aspect
			if ( $this->_lastModified < $aspect->getLastModified() )
			{
                $this->_lastModified = $aspect->getLastModified();
			}

			return true;
		}

		return false;
	}

	/**
	 * @param integer $i
	 * @return Aop_Aspect
	 */
	public function & getAspect( $i )
	{
    	if ( $this->_initialized === false )
    	{
			$this->init();
		}

		if ( array_key_exists( $i, $this->_aspects ) )
		{
			return $this->_aspects[ $i ];
		}

		return null;
	}

	/**
	 * @return array
	 */
	public function & getAspects()
	{
		if ( $this->_initialized === false )
		{
			$this->init();
		}

		return $this->_aspects;
	}

	/**
	 *
	 * @return integer
	 */
	public function getInitialized()
	{
		return $this->_initialized;
	}

	/**
	 * @return integer
	 */
	public function getLastModified()
	{
		return $this->_lastModified;
	}


	/**
	 * @return boolean
	 */
	public function hasAspects()
	{
    	// Provides an alternative to lazy load ( not 100% effective )
		if ( $this->_initialized === false )
		{
    		if ( count( $this->_directories ) > 0 )
    		{
    			return true;
			}

			if ( count( $this->_aspects ) > 0 )
			{
				return true;
			}
    	}

    	return ( $this->getLength() > 0 ) ? true : false;
	}

	/**
	 * Возвращает количество аспектов
	 *
	 * @return unknown
	 */
	public function getLength()
	{
    	if ( $this->_initialized === false )
    	{
			$this->init();
		}

		return count( $this->_aspects );
	}

	/**
	 * "Lazy Initialization"
	 *
	 */
	public function init()
	{
		if ( ( $l = count( $this->_directories ) ) > 0 )
		{
			for ( $i = 0; $i < $l; $i++)
			{
				$pos = $this->_directories[ $i ][ "position" ];
				$dir = $this->_directories[ $i ][ "directory" ];

				$this->_loadDirectory( $dir, $pos );
			}
		}

		// Lazy load flag change
		$this->_initialized = true;
	}


	/**
	 * Загружает рекурсивно содержимое дирректории -- xml файлы с аспектами
	 *
	 * @param str $dir
	 * @param &int $pos
	 */
	protected function _loadDirectory( $dir, & $pos = 0 )
	{
    	// Open Directory Handle
		$handle = dir( $dir );

		// Loop through each item
		while ( false !== ( $file = $handle->read() ) )
		{
			// If it is not an alias
			if ( $file != "." && $file != ".." )
			{
				$file = $dir . "/" . $file;

                // If it is a directory
				if ( is_dir( $file ) )
				{
					$this->_loadDirectory( $file, $pos );
					// If it is a file and a XAD
				}
				else
				{
					$path = pathinfo( $file );
					$mime = mime_content_type( $file );

					if ( ( $mime != "" && $mime == "text/xml" ) ||
						( is_readable( $file ) && $path[ "extension" ] == "xml" ) )
					{
						// Load the aspect.
						$aspect = & Aop_Aspect::from( $file );

						// Define it in Aspects list.
						// Position is relative to defined in Weave constructor.
						// array_splice does work when replacing with objects.
						// As stated in the manual, you have to embed the object in an array.
						array_splice( $this->_aspects, $pos++, 0, array( $aspect ) );
					}
				}
			}
		}

		// Close Directory Handle
		$handle->close();
	}

	/**
	 * Добавление аспектов из объекта Aop_Aspect/XAD( xml с аспектами) файла/дирректории, содержащей XAD файлы
	 *
	 * @param string|array|Aop_Aspect &$item
	 */
	protected function _handleLoad( &$item )
	{
		// If the given argument is an Aspect ( Object )
		if ( $item instanceof Aop_Aspect )
		{
			$this->addAspect( $item );
			// If the given argument is an array of unknown itens
		}
		else if ( is_array( $item ) )
		{
			$l = count( $item );

			for ( $i = 0; $i < $l; $i++)
			{
				$arg = & $item[ $i ];
				$this->_handleLoad( $arg );
			}
			// If the given argument is a directory
		}
		else if ( is_string( $item ) && is_dir( $item ) )
		{
			// One directory may contain more than one file.
			// Prevent server overhead by lazy loading directory's content.
			$i = count( $this->_directories );

			$this->_directories[ $i ][ "position" ] = count( $this->_aspects );
            $this->_directories[ $i ][ "directory" ] = realpath( $item );

            // Analising the most recent last modified ( can be a file )
            $lastMod = filemtime( $this->_directories[ $i ][ "directory" ] );

			if ( $this->_lastModified < $lastMod )
			{
                $this->_lastModified = $lastMod;
			}
			// If the given argument is a XAD
		}
		else if ( is_string( $item ) && is_file( $item ) )
		{
			// Load the Aspect
			$aspect = Miaox_Aop_Aspect::from( $item );

			// Insert the Aspect into Weave
			$this->addAspect( $aspect );
		}
	}


	// Addon
	/**
	 * Возвращает advice, содержащий все методы advice из срезов, загруженных в weave и удовлетворающих условиям
	 *
	 * @param string $className имя класса
	 * @param string имя метода/функции
	 * @param string $pointcutName имя среза
	 * @return Aop_Advice результирующий advice
	 */
	public function & getAdviceFromCustomPointcut( $className, $functionName, $pointcutName )
	{
		$advice = new Aop_Advice();

		$aspects = & $this->getAspects();
		$l = count( $aspects );

		for ( $i = 0; $i < $l; $i++)
		{
			$aspect = & $this->_aspects[ $i ];

			$a = & $aspect->getAdviceFromCustomPointcut(
				$className, $functionName, $pointcutName
			);

			$advice->addData( $a->getData() );
		}

		return $advice;
	}

	/**
	 * Возвращает advice, содержащий все методы advice из срезов, загруженных в weave и удовлетворающих условиям
	 *
	 * @param string $className имя класса
	 * @param string имя метода/функции
	 * @param string $autoPointcut "after"|"before"|"around"
	 * @return Aop_Advice результирующий advice
	 */
	public function & getAdviceFromAutoPointcut( $className, $functionName, $autoPointcut )
	{
		$advice = new Aop_Advice();

		$aspects = $this->getAspects();
		$l = count( $aspects );

		for ( $i = 0; $i < $l; $i++)
		{
			$aspect = & $this->_aspects[ $i ];

			$a = & $aspect->getAdviceFromAutoPointcut(
				$className, $functionName, $autoPointcut
			);
			$advice->addData( $a->getData() );
		}

		return $advice;
	}
}