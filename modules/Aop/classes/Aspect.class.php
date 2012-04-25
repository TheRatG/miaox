<?php
/**
 * UniPG
 * @package Tools
 * @subpackage Tools_Aop
 */

/**
 * Аспект
 *
 * @package Tools
 * @subpackage Tools_Aop
 */
class Miaox_Aop_Aspect
{
	/**
	 * путь к файлу с аспектом
	 *
	 * @var str
	 */
	protected  $_xmlFile;
	/**
	 * флаг ленивой инициализации
	 *
	 * @var boolean
	 */
	protected  $_initialized;
	/**
	 * Время последней модификации файла
	 *
	 * @var int
	 */
	protected  $_lastModified;
	/**
	 * Массив срезов точек
	 *
	 * @var array
	 */
	protected  $_pointcuts;

	/**
	 *  Конструктор класса Aop_Aspect
	 *
	 * @param string $xmlFile путь к файлу
	 * @param boolean $lazyInit do use lazy init
	 */
	public function __construct( $xmlFile, $lazyInit = true )
	{
		$this->_xmlFile = $xmlFile;
		$this->_initialized = false;
		$this->_lastModified = filemtime( $this->_xmlFile );
		$this->_pointcuts = array();
		// Testing purposes
		$this->hash = md5( rand() );

		// Do not initialize here ( compiled version uses less resources )
		if ( $lazyInit === false )
		{
	    	$this->init();
	    }
	}

	/**
	 * Загрузка аспекта из xml файла
	 *
	 * @param str $xmlFile путь к файлу
	 * @param boolean $lazyInit разрешить ленивую инициализацию
	 * @return Aop_Aspect
	 */
	public static function & from( $xmlFile, $lazyInit = true )
	{
		// This static method implements a modified version of Registry Pattern.
		// It adds the possibility to Aspects' Instances to be unique.
		// Prevents extra memory consumption of server.
		static $_instances = array();

		// Generate Xml Aspect Definition ( XAD ) Hash
		$hash = md5( realpath( $xmlFile ) );

		if ( !array_key_exists( $hash, $_instances ) )
		{
			$_instances[ $hash ] = new Miaox_Aop_Aspect( $xmlFile, $lazyInit );
		}

		return $_instances[ $hash ];
	}

	/**
	 * Добавить срез
	 *
	 * @param Aop_Pointcut $pointcut
	 */
	public function addPointcut( &$pointcut )
	{
		$this->_pointcuts[ count( $this->_pointcuts ) ] = & $pointcut;
	}

	/**
	 * Возвращает один срез с индексом $i
	 *
	 * @param int $i индекс в $this->pointcuts
	 * @return Aop_Pointcut
	 */
	public function & getPointcut( $i )
	{
		if ( $this->_initialized === false )
		{
			$this->init();
		}

		if ( array_key_exists( $i, $this->_pointcuts ) )
		{
			return $this->pointcuts[ $i ];
		}

		return null;
	}

	/**
	 * Возвращает все срезы
	 *
	 * @return array
	 */
	public function & getPointcuts()
	{
		if ( $this->_initialized === false )
		{
			$this->init();
		}

		return $this->_pointcuts;
	}

	/**
	 * getter для $this->_xmlFile
	 *
	 * @return string
	 */
	public function getXmlFile()
	{
		return $this->_xmlFile;
	}

	/**
	 * getter $this->_initialized
	 *
	 * @return boolean
	 */
	public function getInitialized()
	{
		return $this->_initialized;
	}

	/**
	 * getter $this->_lastModified
	 *
	 * @return integer
	 */
	public function getLastModified()
	{
		return $this->_lastModified;
	}

	/**
	 * Инициализация объекта
	 *
	 */
	public function init()
	{
		// Checking if file exists
		if ( !file_exists( $this->_xmlFile ) )
		{
			throw new Aop_Exception(
				"<b>[ Aspect Error ]:</b> File <b>" . $this->_xmlFile . "</b> does not exist!" );
		}

		// Get file contents
		$fContent = implode( "", file( $this->_xmlFile ) );

		// Parse XML file into an array map
		$xmlReader = Aop_XmlReader::fromString( $fContent );

		// Solve Advice src directory reference. Changes actual working directory
		$oldWorkDir = getcwd();
		chdir( dirname( realpath( $this->_xmlFile ) ) );

		// Check for Root Element
		if ( strtolower( $xmlReader->getTag() ) == "aspect" )
		{
			foreach ( $xmlReader->getChildNodes() as $k => $v )
			{
    	    	if ( $v->getTag() == "pointcut" )
    	    	{
        			// Check for attributes
					$class = ( $v->getAttribute( "class" ) === null ) ? "" : $v->getAttribute( "class" );
					$function = ( $v->getAttribute( "function" ) === null ) ? "" : $v->getAttribute( "function" );
                    $nclass = ( $v->getAttribute( "nclass" ) === null ) ? "" : $v->getAttribute( "nclass" );
					$nfunction = ( $v->getAttribute( "nfunction" ) === null ) ? "" : $v->getAttribute( "nfunction" );

					// Implement some advice code creation via appending fragments
					$advice = new Aop_Advice();

					foreach ( $v->getChildNodes() as $c => $a )
					{
						// Append Advice fragment, depending from its source ( CDATA or External File )
						if ( $a->getTag() == "advice" )
						{
							$asRequire = ( $a->getAttribute( "asrequire" ) === null ) ? "" : trim( $a->getAttribute( "asrequire" ) );

							if ( $asRequire == "true" )
							{
								$adviceCode = " require \"". realpath( getcwd() . "/" . $a->getAttribute( "src" ) ) ."\"; ";
							}
							else if ( $asRequire == "" || $asRequire == "false" )
							{
                                $adviceCode = implode( "", file( $a->getAttribute( "src" ) ) );

								if ( substr( $adviceCode, 0, 2 ) == "<?" )
								{
	                            	// Crop begin and end of archive
	                            	if ( substr( $adviceCode, 2, 3 ) == "php" )
	                            	{
										$adviceCode = substr( $adviceCode, 5 );
									}
									else
									{
	                                    $adviceCode = substr( $adviceCode, 2 );
									}
								}
								else
								{
									$adviceCode = "?>" . $adviceCode;
								}

								if ( substr( $adviceCode, -2, 2 ) == "?>" )
								{
									$adviceCode = substr( $adviceCode, 0, -2 );
								}
								else
								{
									$adviceCode .= "<?php";
								}
							}
							else
							{
                                throw new Aop_Exception(
									"<b>[ Aspect Error ]:</b> Undefined value <b>" . $asRequire . "</b> for advice tag!" );
							}
						}
						else
						{
                        	$adviceCode = $a->getValue();
						}

						$advice->addData( $adviceCode );
					}

					// Appending AutoPointcut or CustomPointcut to PointcutList
					if ( $v->getAttribute( "name" ) === null && $v->getAttribute( "auto" ) !== null )
					{
                    	$this->addPointcut( new Aop_Pointcut_Auto( $advice, $class, $function, $v->getAttribute( "auto" ), $nclass, $nfunction ) );
					}
					else if ( $v->getAttribute( "name" ) !== null && $v->getAttribute( "auto" ) === null )
					{
                    	$this->addPointcut( new Aop_Pointcut_Custom( $advice, $class, $function, $v->getAttribute( "name" ), $nclass, $nfunction ) );
					}
				}
			}
		}

		// Re-configure Work directory
		chdir( $oldWorkDir );

		// Lazy load flag change
		$this->_initialized = true;
	}


	/**
	 * Возвращает все advice для именнованного среза, удовлетволяющего условиям
	 *
	 * @param string $className имя класса -- фильтр для среза
	 * @param string $functionName имя функции -- фильтр для среза
	 * @param string $pointcutName имя среза -- фильтр
	 * @return Aop_Advice
	 */
	public function & getAdviceFromCustomPointcut( $className, $functionName, $pointcutName )
	{
		$advice = new Aop_Advice();

		$pointcuts = & $this->getPointcuts();
		$l = count( $pointcuts );

		for ( $i = 0; $i < $l; $i++ )
		{
			$pointcut = & $pointcuts[ $i ];

			if ( $pointcut instanceof Aop_Pointcut_Custom &&
			    ( $pointcut->hasClassName( $className ) || $pointcut->hasClassName( "" ) ) &&
				( $pointcut->hasFunctionName( $functionName ) || $pointcut->hasFunctionName( "" ) ) &&
				$pointcut->hasName( $pointcutName ) )
			{
				if ( !( $pointcut->hasNotInClassName( $className ) || $pointcut->hasNotInFunctionName( $functionName ) ) )
				{
					$a = $pointcut->getAdvice();
					$advice->addData( $a->getData() );
				}
				else
				{
					$this->_checkForAttrMatching( $pointcut, $className, $functionName );
				}
			}
		}

		return $advice;
	}

	/**
	 * Возвращает все advice для авто среза, удовлетволяющего условиям
	 *
	 * @param string $className имя класса -- фильтр для среза
	 * @param string $functionName имя функции -- фильтр для среза
	 * @param string $autoPointcut значение из набора ( before|after|around ) -- фильтр
	 * @return Aop_Advice
	 */
	public function & getAdviceFromAutoPointcut( $className, $functionName, $autoPointcut )
	{
		$advice = new Aop_Advice();

		$pointcuts = & $this->getPointcuts();
		$l = count( $pointcuts );

		for ( $i = 0; $i < $l; $i++ )
		{
			$pointcut = & $pointcuts[ $i ];
			if ( $pointcut instanceof Aop_Pointcut_Auto &&
			    ( $pointcut->hasClassName( $className ) || $pointcut->hasClassName( "" ) ) &&
				( $pointcut->hasFunctionName( $functionName ) || $pointcut->hasFunctionName( "" ) ) )
			{
				if ( !( $pointcut->hasNotInClassName( $className ) || $pointcut->hasNotInFunctionName( $functionName ) ) )
				{
					if ( $pointcut->hasAuto( $autoPointcut ) )
					{
						$a = $pointcut->getAdvice();
	                    $advice->addData( $a->getData() );
					}

					if ( $pointcut->hasAuto( "around" ) )
					{
						$a = $pointcut->getAdvice();

						eregi( "(.*)[\t\s \n ]proceed\(\);(.*)", $a->getData(), $data );

						if ( $autoPointcut == "before" )
						{
							$data = ( isset( $data[ 1 ] ) ? $data[ 1 ] : $a->getData() );
						}
						else if ( $autoPointcut == "after" )
						{
							$data = ( isset( $data[ 2 ] ) ? $data[ 2 ] : $a->getData() );
						}
						$advice->addData( $data );
					}
				}
				else
				{
					$this->_checkForAttrMatching( $pointcut, $className, $functionName );
				}
			}
		}
		return $advice;
	}

	/**
	 * Проверка на наличие одинаковых значенией в фильтрах class, nclass и function, nfunction
	 *
	 * @param Aop_Pointcut $pointcut
	 * @param string $className имя класса, для class, nclass
	 * @param string $functionName имя функции, для function, nfunction
	 * @exception Aop_Exception
	 */
	protected function _checkForAttrMatching( &$pointcut, $className, $functionName )
	{
    	// Need to check if there are a matching between class and nclass. Also, nfunction and function
		if ( $className && $pointcut->hasClassName( $className ) && $pointcut->hasNotInClassName( $className ) )
		{
			throw new Aop_Exception(
				"<b>[ Aspect Error ]:</b> Cannot define a pointcut with the same class name [<b>".$className."</b>] in \"class\" and \"nclass\" attribute" );
		}
		else if (
			$functionName
			&& $pointcut->hasFunctionName( $functionName )
			&& $pointcut->hasNotInFunctionName( $functionName ) )
		{
			throw new Aop_Exception(
				"<b>[ Aspect Error ]:</b> Cannot define a pointcut with the same function name [<b>".$functionName."</b>] in \"function\" and \"nfunction\" attribute" );
		} // Does nothing if no match is found!
	}
}
