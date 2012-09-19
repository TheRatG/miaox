<?php
class Miaox_Router_Route_Validator_NotEmpty implements Miaox_Router_Route_Validator_Interface
{
    /**
     * @param mixed $val
     *
     * @return boolean
     */
    public function isValid( $val )
    {
        return !empty($val);
    }

}
