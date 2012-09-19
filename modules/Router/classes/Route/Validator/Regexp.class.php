<?php
class Miaox_Router_Route_Validator_Regexp implements Miaox_Router_Route_Validator_Interface
{
    protected $_regexp;

    public function __construct($regexp)
    {
        $this->_regexp = $regexp;
    }

    public function isValid( $val )
    {
        $ret = preg_match('/'.$this->_regexp.'/', $val, $a);
        return $ret;
    }
}
