<?php
class Miaox_Router_Route_Validator_Numeric implements Miaox_Router_Route_Validator_Interface
{
    /**
     * @param mixed $val
     *
     * @return boolean
     */
    public function isValid( $val )
    {
        return is_numeric( $val );
    }

}
