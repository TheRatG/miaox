<?php
/**
 * UniPG
 * @package Tools
 * @subpackage Tools_Aop
 */

/**
 * @package Tools
 * @subpackage Tools_Aop
 *
 */
class Miaox_Aop_CodeCruncher
{
	/**
	 * конструктор
	 */
	public function __construct()
	{
		if ( !defined( 'T_ML_COMMENT' ) )
		{
			define( 'T_ML_COMMENT', T_COMMENT );
		}
		else
		{
		   	define( 'T_DOC_COMMENT', T_ML_COMMENT );
		}
	}

	/**
	 * process
	 *
	 * @param string $str
	 * @return string
	 */
	public static function process( $str )
	{
		$result = "";
		$codeParser = new Aop_CodeParser( $str );

		while ( ( $token = $codeParser->nextToken() ) !== null )
		{
        	// Internal characters ( ie, (, {, }, ) ) do not have a token_name
			if ( is_array( $token ) )
			{
        		$result .= Aop_CodeCruncher::analizeToken(
					$token,
					$codeParser->getIndex(),
					$codeParser->getInit()
				);
			}
			else if ( is_string( $token ) )
			{
    	      	$result .= $token;
			}
		}

		return trim( $result );
	}

	/**
	 * Разбор блока
	 *
	 * @param array $token
	 * @param integer $i
	 * @param integer $init
	 * @return string
	 */
	public static function analizeToken( $token, $i, $init )
	{
		$result = "";

		switch ( token_name( ( int ) $token[ 0 ] ) )
		{
			case "T_WHITESPACE":
			case "T_ENCAPSED_AND_WHITESPACE":
				// New line between commands fixer
	            $result .= " ";
	            break;

			// [ FIXME ]: Implement a fix for this situation
			case "T_CONSTANT_ENCAPSED_STRING":
				// New line in string definition fixer
				$result .= $token[ 1 ];
				break;

			case "T_OPEN_TAG":
				if ( $i == $init && is_array( $token[ 1 ] ) )
				{
					// Last weird behavior of PHP Tokenizer... it puts
					// the first PHP command as part of the T_OPEN_TAG
					$result .= Aop_CodeCruncher::analizeToken( $token[ 1 ], $i, $init );
				} else
				{
					$result .= trim( $token[ 1 ] );
				}
				break;

			case "T_COMMENT":
			case "T_ML_COMMENT":
			case "T_DOC_COMMENT":
				// Do nothing
				break;

	        default:
	            $result .= $token[ 1 ];
	            break;
	    }

	    return $result;
	}
}