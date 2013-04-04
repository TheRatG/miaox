<?php
/**
 * Snippets.class.Test.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 04.04.13 16:04
 */

require_once 'Helper.class.Test.php';

class Miaox_SphinxQl_Snippet_Test extends Miaox_SphinxQl_Helper_Test
{
    public function testMain()
    {
        $search = $this->_sphinxQl;
        $index = 'articles';
        $opts = array(
            "before_match" => '<span class="mark">',
            "after_match" => "</span>",
            "chunk_separator" => " ... ",
            "limit" => 200,
            "around" => 10
        );
        $docs = array( 'long body test' );
        $actual = $search->callSnippets( $docs, $index, 'test', $opts );
        $expected = array(
            'long body <span class="mark">test</span>',
        );
        $this->assertEquals( $expected, $actual );
    }
}
