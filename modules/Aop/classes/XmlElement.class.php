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
class Miaox_Aop_XmlElement
{
	protected $_tag;
	protected $_value;
	protected $_attributes;
	protected $_childNodes;
	protected $_parentNode;

	/**
	 * @param string $tag
	 */
	public function __construct( $tag = "" )
	{
		if ( $tag != "" )
		{
	    	$this->setTag( $tag );
	    }

	    $this->_attributes = array();
	    $this->_childNodes = array();
	}

	/**
	 * setter $this->_tag
	 *
	 * @param string $tag
	 */
	public function setTag( $tag )
	{
		$this->_tag = $tag;
	}

	/**
	 * @return string
	 */
	public function getTag()
	{
		return $this->_tag;
	}

	/**
	 * setter $this->_value
	 *
	 * @param string $value
	 */
	public function setValue( $value )
	{
		$this->_value = $value;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->_value;
	}

	/**
	 * @param Miaox_Aop_XmlElement $parent
	 */
	public function setParentNode( &$parent )
	{
		$this->_parentNode = & $parent;
	}

	/**
	 * @return Miaox_Aop_XmlElement
	 */
	public function & getParentNode()
	{
		return $this->_parentNode;
	}

	/**
	 * @param array $attrs
	 */
	public function setAttributes( $attrs )
	{
		$this->_attributes = $attrs;
	}

	/**
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->_attributes;
	}

	/**
	 * @param string $attrName
	 * @param string $attrValue
	 */
	public function setAttribute( $attrName, $attrValue )
	{
    	$this->_attributes[ $attrName ] = $attrValue;
	}

	/**
	 * @param string $attrName
	 * @return string
	 */
	public function getAttribute( $attrName )
	{
		if ( $this->hasAttribute( $attrName ) )
		{
			return $this->_attributes[ $attrName ];
		}

		return null;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $attrName
	 * @return boolean
	 */
	public function hasAttribute( $attrName )
	{
		if ( array_key_exists( $attrName, $this->_attributes ) )
		{
			return true;
		}

		return false;
	}

	/**
	 * @param array $nodes
	 */
	public function setChildNodes( $nodes )
	{
		$this->_childNodes = $nodes;
	}

	/**
	 * @return array
	 */
	public function & getChildNodes()
	{
		return $this->_childNodes;
	}

	/**
	 * @param Miaox_Aop_XmlElement $node
	 */
	public function addChildNode( &$node )
	{
		$this->_childNodes[ count( $this->_childNodes ) ] = & $node;
	}

	/**
	 * @param integer $i
	 * @return Miaox_Aop_XmlElement
	 */
	public function & getChildNode( $i )
	{
		if ( array_key_exists( $i, $this->_childNodes ) )
		{
			return $this->_childNodes[ $i ];
		}

		return null;
	}
}
