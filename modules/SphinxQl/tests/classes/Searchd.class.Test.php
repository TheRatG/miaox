<?php
/**
 * Searchd.class.Test.php.
 * @author: vpak <TheRatW@gmail.com>
 * @date: 27.03.13 10:00
*/
require_once __DIR__ . '/../scripts/config.php';
class Miaox_SphinxQl_Searchd_Test
{
    protected $_moduleRoot;

    protected $_host;

    protected $_port;

    protected $_binIndexer;

    protected $_binSearchd;

    public function __construct( $moduleRoot, $host, $port, $binSearchd, $binIndexer )
    {
        $this->_moduleRoot = $moduleRoot;
        $this->_host = $host;
        $this->_port = $port;
        $this->_binIndexer = $binIndexer;
        $this->_binSearchd = $binSearchd;
        $this->_makeSphinxConf();
    }

    public function start()
    {
        $cmd = array();
        $cmd[ ] = sprintf( '%s --config %s --all', $this->_binIndexer, $this->_getSphinxConfFilename() );
        $cmd[ ] = sprintf( '%s --config %s', $this->_binSearchd, $this->_getSphinxConfFilename() );

        $cmd = implode( ' && ', $cmd );
        exec ( $cmd );
    }

    public function stop()
    {
        $cmd = sprintf( '%s --config %s --stop', $this->_binSearchd, $this->_getSphinxConfFilename() );
        exec ( $cmd );
    }

    protected function _makeSphinxConf()
    {
        $sphinxConfTemplate = $this->_moduleRoot . '/sources/sphinx.conf.tpl';
        $sphinxConf = $this->_getSphinxConfFilename();

        $str = file_get_contents( $sphinxConfTemplate );
        $search = array( '{host}', '{port}', '{data_path}', '{source_path}' );
        $replace = array( $this->_host, $this->_port, $this->_moduleRoot . '/data', $this->_moduleRoot . '/sources' );
        $str = str_replace( $search, $replace, $str );
        file_put_contents( $sphinxConf, $str );
    }

    protected function _getSphinxConfFilename()
    {
        $sphinxConf = $this->_moduleRoot . '/data/sphinx.conf';
        return $sphinxConf;
    }
}
