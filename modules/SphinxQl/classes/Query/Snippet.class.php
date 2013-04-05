<?php
/**
 * Snippet.class.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 04.04.13 16:14
 */
class Miaox_SphinxQl_Query_Snippet extends Miaox_SphinxQl_Query
{
    protected $_docs;

    protected $_index;

    protected $_query;

    protected $_options;

    public function __construct( $docs, $index, $query, $options )
    {
        $this->setDocs( $docs );
        $this->setIndex( $index );
        $this->setQuery( $query );
        $this->setOptions( $options );
    }

    public function getDocs()
    {
        return $this->_docs;
    }

    public function setDocs( $docs )
    {
        if ( !is_array( $docs ) )
        {
            $docs = array(
                $docs
            );
        }
        $this->_docs = $docs;
    }

    public function getIndex()
    {
        return $this->_index;
    }

    public function setIndex( $index )
    {
        if ( !is_array( $index ) )
        {
            $index = array(
                $index
            );
        }
        $this->_index = $index;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function setOptions( $opts )
    {
        $this->_options = $opts;
    }

    public function getQuery()
    {
        return $this->_query;
    }

    public function setQuery( $query )
    {
        $this->_query = $query;
    }

    public function compile()
    {
        $result = $this->_buildCallSnippets(
            $this->getDocs(), $this->getIndex(), $this->getQuery(), $this->getOptions()
        );
        return $result;
    }

    protected function _buildCallSnippets( array $docs, $index, $query, $opts )
    {
        $args = array();
        $parts = array();
        foreach ( $docs as $item )
        {
            $parts[ ] = $this->_quote( $item );
        }
        $args[ ] = '( ' . implode( ', ', $parts ) . ' )';
        $args[ ] = "'" . implode( ', ', $index ) . "'";
        $args[ ] = "'" . $query . "'";
        foreach ( $opts as $optKey => $optValue )
        {
            $optValue = $this->_quote( $optValue );
            $args[ ] = $optValue . " AS " . $optKey;
        }
        $args = implode( ', ', $args );
        $result = sprintf( 'CALL SNIPPETS( %s )', $args );
        return $result;
    }
}
