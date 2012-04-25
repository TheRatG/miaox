<?php
/**
 * UniPG
 * @package Tools
 */

/**
 * @package Tools
 * @subpackage Tools_Aop
 *
 */
class Miaox_Aop_XmlReader
{
	/**
	 * @var Aop_CodeParser
	 */
	protected $_parser;
	protected $_curEl;
	protected $_documentElement;


	/**
	 * конструктор
	 *
	 */
	public function __construct()
	{
		$this->_curEl = null;

		$this->_parser = xml_parser_create();

        xml_set_object( $this->_parser, $this );
        xml_parser_set_option( $this->_parser, XML_OPTION_CASE_FOLDING, 0 );

        xml_set_element_handler( $this->_parser, "_startElement", "_endElement" );
        xml_set_character_data_handler( $this->_parser, "_cdataElement" );
	}

	/**
	 * @param string $xmlContent
	 * @return Aop_XmlElement
	 */
	static public function & fromString( $xmlContent )
	{
		$xmlReader = new Aop_XmlReader( $xmlContent );
		return $xmlReader->_parse( $xmlContent );
	}

	/**
	 * @param string $data
	 * @return Aop_XmlElement
	 */
	protected function & _parse( $data )
    {
		if ( !xml_parse( $this->_parser, $data ) )
		{
			$errMessage = xml_error_string( xml_get_error_code( $this->_parser ) );
			$errLine = xml_get_current_line_number( $this->_parser );

			xml_parser_free( $this->_parser );

			throw new Aop_Exception(
				sprintf( "XML error: %s at line %d", $errMessage, $errLine )
			);
		}

		xml_parser_free( $this->_parser );

		return $this->_documentElement;
	}

	/**
	 * @param XML parser $parser
	 * @param string $tagName
	 * @param array $attrs
	 */
	protected function _startElement( $parser, $tagName, $attrs )
	{
		if ( strtolower( $tagName ) == "pointcut" )
		{
			if ( array_key_exists( "name", $attrs ) && array_key_exists( "auto", $attrs ) )
			{
				throw new Aop_Exception(
					"<b>[ Aspect Error ]:</b> Pointcut Node can not have 'name' and 'auto' attributes defined together!" );
			}
			else if ( !array_key_exists( "name", $attrs ) && !array_key_exists( "auto", $attrs ) )
			{
				throw new Aop_Exception(
					"<b>[ Aspect Error ]:</b> Pointcut Node does not have a 'name' or 'auto' defined attribute!" );
			}
		}

		$el = new Aop_XmlElement( $tagName );
		$el->setAttributes( $attrs );
		$el->setParentNode( $this->_curEl );

		if ( $this->_curEl !== null )
		{
			$this->_curEl->addChildNode( $el );
		}
		else
		{
			$this->_documentElement = & $el;
		}

		$this->_curEl = & $el;
	}

	/**
	 * @param XML parser $parser
	 * @param string $tagName
	 */
	protected function _endElement( $parser, $tagName )
	{
		if ( $this->_curEl !== null )
		{
			if ( $this->_curEl->getTag() != $tagName )
			{
				throw new Aop_Exception(
					"<b>[ Aspect Error ]:</b> XML Node '" . $tagName . "' is not in the right inheritance!" );
			}

			$this->_curEl = & $this->_curEl->getParentNode();
		}
	}

	/**
	 * @param XML parser $parser
	 * @param string $data
	 */
	protected function _cdataElement( $parser, $data )
	{
		if ( strlen( trim( $data ) ) > 0 )
		{
			$cdata = new Aop_XmlElement( "#text" );
			$cdata->setValue( $data );
			$cdata->setParentNode( $this->_curEl );

			$this->_curEl->addChildNode( $cdata );
	    }
	}
}
