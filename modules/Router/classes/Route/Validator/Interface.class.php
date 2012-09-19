<?php
interface Miaox_Router_Route_Validator_Interface
{
    /**
     * @param mixed $val
     * @return boolean
     */
    public function isValid($val);
}