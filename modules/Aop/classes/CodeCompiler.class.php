<?php
/**
 * UniPG
 * @package Tools
 * @subpackage Tools_Aop
 */

/**
 * Основной класс, реализующий компиляцию исходных файлов
 *
 * @package Tools
 * @subpackage Tools_Aop
 */
class Miaox_Aop_CodeCompiler
{
	/**
	 * Source Code
	 *
	 * @var string
	 */
    protected  $_code;
    /**
     * Code Parser
     *
     * @var Miaox_Aop_CodeParser
     */
	protected $_codeParser;

	protected $_defStack;
	protected $_defPointcuts;

	/**
	 *
	 *
	 * @var Miaox_Aop_Weave
	 */

	protected $_weave;

	/**
	 * Флаг использования "compact mode"
	 *
	 * @var boolean
	 */
	protected $_compact;
	/**
	 * Enter description here...
	 *
	 * @var bool
	 */
	protected $_returned;

	/**
	 * Конструктор
	 *
	 * @param string $sCode
	 * @param Miaox_Aop_Weave $weave
	 * @return Miaox_Aop_CodeCompiler
	 */
    public function __construct( $sCode, $weave )
    {
    	$this->_defStack = array();
    	$this->_defPointcuts = array();
    	$this->_codeParser = null;

		// Store initial Code Source
		$this->_code = $sCode;

		// Store the aspectList
		$this->_weave = $weave;

		// Define compact mode
		$this->_compact = true;

		// Define returned as false
		$this->_returned = false;
    }


    /**
     * getter $this->_code
     *
     * @return string
     */
    public function getCode()
    {
    	return $this->_code;
    }

    /**
     * Вызывает последовательность методов для преобразования текста
     *
     * @param bool $compact использовать "compact mode"
     */

	public function compile( $compact = true )
	{
		// Compact Mode
		$this->_compact = ( bool ) $compact;

		// Correct missing curly braces in Original Code
		$this->_compileCurlyBracesMissings();

		// Pre-Compile User Defined Pointcuts
		$this->_compileCustomPointcuts();

		// Correct missing curly braces in Custom Pointcuts
	 	$this->_compileCurlyBracesMissings();

        // Compile Automatic Pointcuts
		$this->_compileAutoPointcuts();

		// Some PHP versions remove the PHP Close Tag...
		// Check for it and include if necessary
		if ( substr( $this->_code, strlen( $this->_code ) - 2, 2 ) != "?>" )
		{
			$this->_code .= "?>";
		}
	}

	/**
	 * Обрабатывает named pointcut
	 *
	 */
	protected function _compileCustomPointcuts()
	{
    	// Create Code Parser
		$this->_codeParser = new Miaox_Aop_CodeParser( $this->_code );
		$this->_codeParser->setIndex( 0 );

        // Blank result string
		$result = "";

        // Loop through each PHP token
		while ( ( $tok = $this->_codeParser->currentToken() ) !== null )
		{
			$result .= $this->_analizeCustomToken( $tok );
			$this->_codeParser->nextToken();
		}

		// Assigning properties
		$this->_code = $result;
		$this->_codeParser = null;
	}


	/**
	 * Разбирает named pointcut
	 *
	 * @param string|array $token
	 * @return string
	 */
	protected function _analizeCustomToken( $token )
	{
		$result = "";

        if ( !is_array( $token ) )
        {
			$result .= $token;

			switch ( $token )
			{
				case "{":
					// Include the curly open into stack
					array_push( $this->_defStack, "{" );

					break;

				case "}":
					// Just process if there's at least a function or class defined
					if ( count( $this->_defStack ) >= 2 )
					{
						// Retrieve possible curly open and method name
						$curly = array_pop( $this->_defStack );
						$method = array_pop( $this->_defStack );

						if ( $curly == "{" && ( $method == "{" || $method == "}" ) )
						{
							array_push( $this->_defStack, $method );
						}
					}
					else
					{
						// Remove class definition and/or garbage
						$this->_defStack = array();
					}

					break;
			}
        }
        else
        {
        	switch ( token_name( ( int ) $token[ 0 ] ) )
        	{
				case "T_CLASS":
				case "T_FUNCTION":
					$result .= $this->_compileCustomClassOrMethodToken( $token );
					break;

	            case "T_COMMENT":
	        		$result .= $this->_compileCustomCommentToken( $token );
					break;

	            default:
	            	// Append token into result string
					$result .= $token[ 1 ];
					break;
	        }
        }

		return $result;
	}

	/**
	 * Разбор блока
	 *
	 * @param array $token
	 * @return string
	 */
	protected function _compileCustomClassOrMethodToken( $token )
	{
        // Append token into result string
		$result = $token[ 1 ];

		$tk = $this->_codeParser->nextToken();

        // Finding the next parsable token
        while (
        	!is_array( $tk )
        	|| ( is_array( $tk )
        	&& token_name( ( int ) $tk[ 0 ] ) != "T_STRING" )
        )
        {
            // T_WHITESPACE or &
    		$tk = $this->_codeParser->nextToken();
        }

        // T_STRING
        $nextToken = $this->_codeParser->currentToken();

		// Include the class/function name into stack
		array_push( $this->_defStack, $nextToken[ 1 ] );

		$tk = $this->_codeParser->currentToken();

        // Returning to the last parsable token
        while (
        	!is_array( $tk )
        	|| ( is_array( $tk )
        	&& ( token_name( ( int ) $tk[ 0 ] ) != "T_CLASS"
        	&& token_name( ( int ) $tk[ 0 ] ) != "T_FUNCTION" ) )
        )
        {
            // Anything else: T_CLASS / T_FUNCTION
    		$tk = $this->_codeParser->previousToken();
        }

		return $result;
	}

	/**
	 * Разбор блока с комментарием
	 *
	 * @param array $token
	 * @return string
	 */
	protected function _compileCustomCommentToken( $token )
	{
		$result = "";

		// Just process if there's at least a function or class defined
		if ( count( $this->_defStack ) >= 2 )
		{
			preg_match_all( "/\/\/\/\s*Pointcut\s*:\s*([^\r\n]*)/i", $token[ 1 ], $pointcut, PREG_OFFSET_CAPTURE );

            // Pointcuts found?
			if ( is_array( $pointcut ) && count( $pointcut ) > 0 && count( $pointcut[ 0 ] ) > 0 )
			{
				// Retrieve the class name
				$class = array_shift( $this->_defStack );

				// Look for method name
				$method = "";

				for ( $i = count( $this->_defStack ) - 1; $i >= 0; $i--)
				{
					if ( $this->_defStack[ $i ] != "{" && $this->_defStack[ $i ] != "}" )
					{
						$method = $this->_defStack[ $i ];
						break;
					}
				}

				// Grab the Advice code
				$advice = & $this->getAdviceFromCustomPointcut( $class, $method, $pointcut[ 1 ][ 0 ][ 0 ] );
				$result .= $advice->getData();// . "\r\n";

				// Put the class name back into stack
				array_unshift( $this->_defStack, $class );
			}
			else
			{
				// Append token into result string
				$result .= $token[ 1 ];
			}
		}
		else
		{
			// Append token into result string
			$result .= $token[ 1 ];
		}

		return $result;
	}

	/**
	 * Исправление скобок
	 *
	 */
	protected function _compileCurlyBracesMissings()
	{
		// Inserting missing braces ( does only match up to 2 nested parenthesis )

	    $this->_code = preg_replace( "/(if|for|while|switch)\s*(\([^()]*(\([^()]*\)[^()]*)*\))([^{;]*;)/i", "\\1 \\2 {\\4 }", $this->_code );

		// [ FIXME ] Missing braces for else statements
	    $this->_code = preg_replace( "/(else)\s*([^{;]*;)/i", "\\1 {\\2 }", $this->_code );
	}

	/**
	 * Обрабтка auto pointcut
	 *
	 */
	protected function _compileAutoPointcuts()
	{
		// Create Code Parser
		$this->_codeParser = new Miaox_Aop_CodeParser( $this->_code );
		$this->_codeParser->setIndex( 0 );

		// Blank result string
		$result = "";

		// Loop through each PHP token
		while ( ( $tok = $this->_codeParser->currentToken() ) !== null )
		{
			$result .= $this->_analizeAutoToken( $tok );

			$this->_codeParser->nextToken();
		}

		// Assigning properties
		$this->_code = $result;
		$this->_codeParser = null;
	}

	/**
	 * Разбор auto poincut
	 *
	 * @param string|array $token
	 * @return unknown
	 */
	protected function _analizeAutoToken( $token )
	{
		$result = "";

        if ( !is_array( $token ) )
        {
        	switch ( $token )
        	{
				case "{":
					$result .= $this->_compileAutoCurlyOpenToken();
					break;

				case "}":
                    $result .= $this->_compileAutoCurlyCloseToken();
					break;

				default:
					$result .= $token;
					break;
			}
        }
        else
        {
        	switch ( token_name( ( int ) $token[ 0 ] ) )
        	{
				case "T_CLASS":
				case "T_FUNCTION":
					$result .= $this->_compileAutoClassOrMethodToken( $token );
					break;

				case "T_EXIT":
				case "T_RETURN":
					$result .= $this->_compileAutoExitOrReturnToken( $token );
					break;

	            default:
	            	// Append token into result string
					$result .= $token[ 1 ];
					break;
	        }
        }
		return $result;
	}


	/**
	 * Разбор auto poincut
	 *
	 * @return string
	 */
	protected function _compileAutoCurlyOpenToken()
	{
		// Append token into result string
		$result = "{";

		// Just process if there's at least a function or class defined
		if ( count( $this->_defStack ) >= 1 )
		{
			// Retrieve the possible method name
			$method = array_pop( $this->_defStack );

            // Check for inner definition of curly. If the last defined token
			// is not a method name, do not use it.
			if ( is_array( $method ) )
			{
				// Retrieve the class name
				$class = array_shift( $this->_defStack );

				// Check if it is a function or a method
				if (
					( $class === null || ( is_array( $class ) && $class[ 0 ] === "class" ) )
					&& $method[ 0 ] !== "class"
				)
				{
					// Grab the Advice code
					$advice = & $this->getAdviceFromAutoPointcut( $class[ 1 ], $method[ 1 ], "before" );
					$result .= " " . $advice->getData();
				}

				// If it is a method ( $class contains a class token ), put back on stack
				if ( $class !== null )
				{
					// Put the class name back into stack
					array_unshift( $this->_defStack, $class );
				}
			}

			// Put the method, curly or other token back to the stack
			array_push( $this->_defStack, $method );
		}

		// Include the curly open into stack
		array_push( $this->_defStack, "{" );

		return $result;
	}

	/**
	 * Разбор auto poincut
	 *
	 * @return string
	 */
	protected function _compileAutoCurlyCloseToken()
	{
		$result = "";

		// Just process if there's at least a function or class defined
		if ( count( $this->_defStack ) >= 1 )
		{
			// Retrieve possible curly open and method name
			$curly = array_pop( $this->_defStack );
			$method = array_pop( $this->_defStack );

			// Check if it's really a method definition
			if ( !is_array( $curly ) && $curly == "{" && is_array( $method ) )
			{
				// Retrieve the class name
				$class = array_shift( $this->_defStack );

                // Check if it is a function or a method
				if ( ( $class === null || ( is_array( $class ) && $class[ 0 ] === "class" ) )
					&& $method[ 0 ] !== "class" && $this->_returned === false
				)
				{
					// Grab the Advice code
					$advice = & $this->getAdviceFromAutoPointcut( $class[ 1 ], $method[ 1 ], "after" );
					$result .= $advice->getData();
				}

                // If it is a method ( $class contains a class token ), put back on stack
                if ( $class !== null )
                {
					// Put the class name back into stack
					array_unshift( $this->_defStack, $class );
				}

				$this->_returned = false;
			}
			else if ( ( !is_array( $curly ) && $curly == "{" ) && ( !is_array( $method ) && ( $method == "{" || $method == "}" ) ) )
			{
				// Put the function, curly or other token back to the stack
				array_push( $this->_defStack, $method );
			}
		}
		else
		{
			// Remove class definition and/or garbage
			$this->_defStack = array();
		}

        // Append token into result string
		return $result . "}";
	}

	/**
	 * Разбор блока
	 *
	 * @param array $token
	 * @return string
	 */
	protected function _compileAutoClassOrMethodToken( $token )
	{
		// Append token into result string
		$result = $token[ 1 ];

        $tk = $this->_codeParser->nextToken();

        // Finding the next parsable token
        while ( !is_array( $tk ) || ( is_array( $tk ) && token_name( ( int ) $tk[ 0 ] ) != "T_STRING" ) )
        {
            // T_WHITESPACE or &
    		$tk = $this->_codeParser->nextToken();
        }

        // T_STRING
        $nextToken = $this->_codeParser->currentToken();

        // Include the class/function name into stack
		array_push( $this->_defStack, array( $token[ 1 ], $nextToken[ 1 ] ) );

        $tk = $this->_codeParser->currentToken();

        // Returning to the last parsable token
        while ( !is_array( $tk ) || ( is_array( $tk ) && ( token_name( ( int ) $tk[ 0 ] ) != "T_CLASS" && token_name( ( int ) $tk[ 0 ] ) != "T_FUNCTION" ) ) )
        {
            // Anything else: T_CLASS / T_FUNCTION
    		$tk = $this->_codeParser->previousToken();
        }

		return $result;
	}

	/**
	 * Разбор блока
	 *
	 * @param array $token
	 * @return string
	 */
	protected function _compileAutoExitOrReturnToken( $token )
	{
		$result = "";

		// Retrieve the class name
		$class = array_shift( $this->_defStack );

		// Look for method name
		$method = "";

		for ( $i = count( $this->_defStack ) - 1; $i >= 0; $i--)
		{
			if ( is_array( $this->_defStack[ $i ] ) )
			{
				$method = $this->_defStack[ $i ];
				break;
			}
		}

		// Retrieve defined user code
		$advice = & $this->getAdviceFromAutoPointcut( $class[ 1 ], $method[ 1 ], "after" );
		$code = $advice->getData();

		// Add space if any code is defined
    	if ( strlen( $code ) > 0 )
    	{
			$result .= $code . " ";
		}

		// Retrieve token name
		$tokenName = token_name( ( int ) $token[ 0 ] );

		// Append token into result string
		$result .= ( $tokenName == "T_EXIT" ) ? "exit" : "return";

		// Put the class name back into stack
		array_unshift( $this->_defStack, $class );

		// Check if last statement is a return ( inclusion of advice after the last command as return )
		if (
			( is_array( $class ) && $class[ 0 ] === "class" && count( $this->_defStack ) < 5 )
			|| count( $this->_defStack ) < 3
		)
		{
        	$this->_returned = true;
		}

		return $result;
	}

	/**
	 * Возвращает код advice секции для named pointcut
	 *
	 * @param string $class
	 * @param string $method
	 * @param string $pointcutName
	 * @return Miaox_Aop_Advice
	 */
	public function & getAdviceFromCustomPointcut( $class, $method, $pointcutName )
	{
		$advice = new Miaox_Aop_Advice();

		$a = & $this->_weave->getAdviceFromCustomPointcut( $class, $method, $pointcutName );
		$code = $a->getData();

		// Does it has any code to replace?
		if ( strlen( $code ) > 0 )
		{
			// PHP Code Cruncher
			if ( $this->_compact )
			{
				$code = Miaox_Aop_CodeCruncher::process( $code );
			}
			else
			{
				$code = "\r\n" . $code . "\r\n";
			}

			// Add an informative text
			$code = "/* AOP \"" . $pointcutName . "\" Code */ " . $code . " ";
		}

		$advice->addData( $code );

		return $advice;
	}

	/**
	 * Возвращает код advice секции для auto pointcut
	 *
	 * @param string $class
	 * @param string $method
	 * @param string $autoPointcut
	 * @return Miaox_Aop_Advice
	 */
	public function & getAdviceFromAutoPointcut( $class, $method, $autoPointcut )
	{
		$advice = new Miaox_Aop_Advice();

		$a = & $this->_weave->getAdviceFromAutoPointcut( $class, $method, $autoPointcut );
		$code = $a->getData();

		// Does it has any code to replace?
		if ( strlen( $code ) > 0 )
		{
			// PHP Code Cruncher
			if ( $this->_compact )
			{
				$code = Miaox_Aop_CodeCruncher::process( $code );
			}
			else
			{
				$code = "\r\n" . $code . "\r\n";
			}

			// Add an informative text
			$code = "/* AOP \"" . $autoPointcut . "\" Auto Code */ " . $code . " ";
		}

		$advice->addData( $code );

		return $advice;
	}
}