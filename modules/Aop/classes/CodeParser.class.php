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
class Miaox_Aop_CodeParser
{
	protected $_init;
	protected $_length;

	protected $_code;
	protected $_tokens;

	protected $_index;
	protected $_invalidTokens;
	protected $_validTokens;

	/**
	 * конструктор
	 *
	 * @param string $sCode
	 */
	public function __construct( $sCode )
	{
		$this->_code = ( trim( substr( $sCode, 0, 2 ) ) == "<?" ) ? $sCode : "<?php " . $sCode . " ?>";

		// Reference: http://bugs.php.net/bug.php?id=28391
		$this->_tokens = token_get_all( $this->_code );

		// PHP Tokenizer has different approaches here; sometimes it considers
		// < ? php as 3 itens, other items it just removes it. The same to ? >.
		// This piece of code tries to solve these differences between versions
		// of PHP Tokenizer Engine.

		// Initial OPENTAG
		$this->_init = 0; // 0: considering PHP Tokenizer removed PHP Tag Open

		// Initial CLOSETAG
	    $this->_length = count( $this->_tokens ) - 1; // count() - 1: possible PHP Tag Close

        if ( count( $this->_tokens ) > 0 )
        {
			// Correcting OPENTAG
			if ( !is_array( $this->_tokens[ 0 ] ) && $this->_tokens[ 0 ] == "<" )
			{
				// 3: PHP Tag Open - 0 => <, 1 => ?, 2 => php
				$this->_init = 3;
			}
			else if ( is_array( $this->_tokens[ 0 ] ) && token_name( $this->_tokens[ 0 ][ 0 ] ) == "T_OPEN_TAG" )
			{
				// 1: PHP Tag Open - 0 => < ? php
				$this->_init = 1;
			}

            // Correcting CLOSETAG
			if ( is_array( $this->_tokens[ $this->_length ] )
				&& token_name( $this->_tokens[ $this->_length ][ 0 ] )
				!= "T_CLOSE_TAG"
			)
			{
				$this->_length++; // Increase one ( same as $l = count( $tok );)
			}
		}

		$this->_index = $this->_init;

		// Initial Invalid Tokens ( blank array means none )
		$this->_invalidTokens = array();

		// Initial Valid Tokens ( blank array means all )
		$this->_validTokens = array();
	}

	/**
	 * Возвращает блок по индексу
	 *
	 * @param integer $i
	 * @return mixed
	 */
	public function getToken( $i )
	{
		if ( $i >= 0 && $i < $this->_length )
		{
			return $this->_tokens[ $i ];
		}

		return null;
	}

	/**
	 * Возвращает имя блока
	 *
	 * @param integer $i
	 * @return string|null
	 */
	public function getTokenName( $i )
	{
		if ( $i >= 0 && $i < $this->_length )
		{
			$token = $this->_tokens[ $this->_index ];
			return ( is_array( $token ) ? $token[ 1 ] : $token );
		}

		return null;
	}

	/**
	 * setter для $this->_validTokens
	 *
	 * @param mixed $validTokens
	 */
	public function setValidTokens( $validTokens = array() )
	{
		if ( !is_array( $validTokens ) )
		{
        	$validTokens = array( $validTokens );
		}

		$this->_validTokens = $validTokens;
	}

	/**
	 * getter для $this->_validTokens
	 *
	 * @return array $validTokens
	 */
	public function getValidTokens()
	{
		return $this->_validTokens;
	}

	/**
	 * setter для $this->_invalidTokens
	 *
	 * @param mixed $invalidTokens
	 */
	public function setInvalidTokens( $invalidTokens = array() )
	{
		if ( !is_array( $invalidTokens ) )
		{
        	$invalidTokens = array( $invalidTokens );
		}

		$this->_invalidTokens = $invalidTokens;
	}

	/**
	 * getter для $this->_invalidTokens
	 *
	 * @return array
	 */
	public function getInvalidTokens()
	{
		return $this->_invalidTokens;
	}

	/**
	 * setter $this->_index
	 * @deprecated судя по коменту :)
	 * @param integer $i
	 */
	public function setIndex( $i )
	{
		// Only use this method if you are REALLY SURE of what you're doing
		$this->_index = $i;
	}

	/**
	 * getter для $this->_index
	 *
	 * @return integer
	 */
	public function getIndex()
	{
		return $this->_index;
	}

	/**
	 * getter для $this->_init
	 *
	 * @return integer
	 */
	public function getInit()
	{
		return $this->_init;
	}

	/**
	 * getter для $this->_length
	 *
	 * @return integer
	 */
	public function getLength()
	{
		return $this->_length;
	}

	/**
	 * getter для $this->_code
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->_code;
	}

	/**
	 * Возвращает текущий блок
	 *
	 * @return mixed
	 */
	public function currentToken()
	{
		return $this->nextToken( false );
	}

	/**
	 * Возвращает имя текущего блока
	 *
	 * @return string
	 */
	public function currentTokenName()
	{
		return $this->nextTokenName( false );
	}

	/**
	 * Возвращает текущий или следующий блок
	 *
	 * @param boolean $increment текущий или следущий
	 * @return mixed
	 */
	public function nextToken( $increment = true )
	{
		if ( $increment )
		{
			$this->increment();
		}

		return $this->getToken( $this->_index );
	}

	/**
	 * Возвращает имя текущего или следующего блока
	 *
	 * @param boolean $increment текущий или следущий
	 * @return mixed
	 */
	public function nextTokenName( $increment = true )
	{
    	if ( $increment )
    	{
			$this->increment();
		}

		return $this->getTokenName( $this->_index );
	}

	/**
	 * Возвращает текущий или предидущий блок
	 *
	 * @param boolean $increment текущий или предидущий
	 * @return mixed
	 */
	public function previousToken( $decrement = true )
	{
		if ( $decrement )
		{
			$this->decrement();
		}

		return $this->getToken( $this->_index );
	}

	/**
	 * Возвращает имя текущего или предидущего блока
	 *
	 * @param boolean $increment текущий или предидущий
	 * @return mixed
	 */
    public function previousTokenName( $decrement = true )
	{
		if ( $decrement )
		{
			$this->decrement();
		}

		return $this->getTokenName( $this->_index );
	}

	/**
	 * iterator->rewind ( $this->_index = $this->_init )
	 *
	 */
	public function reset()
	{
		$this->_index = $this->_init;
	}

	/**
	 * $this->_index++
	 *
	 */
	public function increment()
	{
    	do
    	{
			$this->_index++;

			// PHP 4 Parser error prevention
			if ( array_key_exists( $this->_index, $this->_tokens ) )
			{
				$token = $this->_tokens[ $this->_index ];
			}
			else
			{
				$this->_index = $this->_length;
			}
		}
		while ( $this->_index < $this->_length && ( $this->isInvalidToken( $token ) || !$this->isValidToken( $token ) ) );
	}

	/**
	 * $this->_index--
	 *
	 */
	public function decrement()
	{
        do
        {
			$this->_index--;

			// PHP 4 Parser error prevention
			if ( array_key_exists( $this->_index, $this->_tokens ) )
			{
				$token = $this->_tokens[ $this->_index ];
			}
			else
			{
				$this->_index = -1;
			}
		}
		while ( $this->_index >= 0 && ( $this->isInvalidToken( $token ) || !$this->isValidToken( $token ) ) );
	}

	/**
	 * Checkает блок
	 *
	 * @param mixed $token
	 * @return boolean
	 */
	public function isInvalidToken( $token )
	{
		$tokenType = ( is_array( $token ) ) ? token_name( ( int ) $token[ 0 ] ) : $token;

		if ( count( $this->_invalidTokens ) < 1 )
		{
			return false;
		}

		$return = array_search( $tokenType, $this->_invalidTokens );

		return ( $return !== null && $return !== false );
	}

	/**
	 * Checkает блок
	 *
	 * @param mixed $token
	 * @return boolean
	 */
   	public function isValidToken( $token )
	{
		$tokenType = ( is_array( $token ) ) ? token_name( ( int ) $token[ 0 ] ) : $token;

		if ( count( $this->_validTokens ) < 1 )
		{
			return true;
		}

		$return = array_search( $tokenType, $this->_validTokens );

		return ( $return !== null && $return !== false );
	}
}
