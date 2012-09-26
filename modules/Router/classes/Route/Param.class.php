<?php
class Miaox_Router_Route_Param
{
    /**
     * @var string
     */
    protected $_name;

    /**
     * @var string
     */
    protected $_regexp;

    /**
     * @var string
     */
    protected $_value = null;

    /**
     * @var Miaox_Router_Route_Validator_Interface[]
     */
    protected $_validators = array ();

    function __construct($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param Miaox_Router_Route_Validator_Interface $validator
     */
    public function addValidator(Miaox_Router_Route_Validator_Interface $validator)
    {
        $this->_validators[] = $validator;
    }

    /**
     * @param string $val
     *
     * @return bool
     */
    public function isValid($val)
    {
        $ret = true;
        foreach ($this->_validators as $validator)
        {
            if (!$validator->isValid($val))
            {
                $ret = false;
            }
        }
        return $ret;
    }

    /**
     * @param $value
     */
    public function setValue($value)
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

}
