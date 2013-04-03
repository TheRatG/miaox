<?php
/**
 * Match.class.Test.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 02.04.13 15:30
 */
require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_Match_Test extends Miaox_SphinxQl_Helper_Test
{
    /**
     * @var Miaox_SphinxQl
     */
    private $_sphinxQl;

    public function setUp()
    {
        $this->_sphinxQl = new Miaox_SphinxQl( SEARCHD_HOST, SEARCHD_PORT );
    }

    public function tearDown()
    {
        unset( $this->_sphinxQl );
    }

    public function testMatchScan()
    {
        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->match( 'article 2' );

        $actual = $search->execute();
        $expected = array(
            0 => array(
                'id' => '2',
                'publish_date' => '1363845000',
                'type' => '1',
            ),
        );
        $this->assertEquals( $expected, $actual );

        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->match( '', 'article 2' );

        $this->assertEquals( $expected, $actual );
    }

    public function testMatchField()
    {
        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->match( '@tags', '555' );

        $actual = $search->execute();
        $expected = array(
            0 => array(
                'id' => '1',
                'publish_date' => '1132223498',
                'type' => '1',
            ),
        );
        $this->assertEquals( $expected, $actual );

        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->match( '@tags', '555|666' );
        $actual = $search->execute();
        $expected = array(
            0 => array(
                'id' => '1',
                'publish_date' => '1132223498',
                'type' => '1',
            ),
            1 => array(
                'id' => '2',
                'publish_date' => '1363845000',
                'type' => '1',
            ),
            2 => array(
                'id' => '4',
                'publish_date' => '1364017800',
                'type' => '1',
            ),
        );
        $this->assertEquals( $expected, $actual );

        $search
            ->select()
            ->from( 'articles' )
            ->match( 'tags', '555|666' );

        $actual = $search->execute();
        $this->assertEquals( $expected, $actual );
    }

    public function testMatchMixed()
    {
        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->match( '@tags', '555' )
            ->match( 'article 1' );
        $actual = $search->execute();
        $expected = array(
            0 => array(
                'id' => '1',
                'publish_date' => '1132223498',
                'type' => '1',
            ),
        );
        $this->assertEquals( $expected, $actual );
    }

    public function testMatchSpecial()
    {
        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->match( '^OneWordTitle$' );
        $actual = $search->execute();
        $expected = array(
            0 => array(
                'id' => '6',
                'publish_date' => '1364194200',
                'type' => '4',
            ),
        );
        $this->assertEquals( $expected, $actual );
    }

    public function testMatchEscape()
    {
        $search = $this->_sphinxQl;
        $search
            ->select()
            ->from( 'articles' )
            ->match( 'body', '$200', true );
        $actual = $search->execute();
        $expected = array(
            0 => array(
                'id' => '5',
                'publish_date' => '1364107800',
                'type' => '3',
            ),
        );
        $this->assertEquals( $expected, $actual );
    }
}
