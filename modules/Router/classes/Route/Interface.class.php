<?php
interface Miaox_Router_Route_Interface
{
    /**
     * @param string $view
     */
    public function setView($view);

    /**
     * @param string $action
     */
    public function setAction($action);

    /**
     * @return string
     */
    public function getView();

    /**
     * @return string
     */
    public function getAction();

    /**
     * @return Miaox_Router_Route_Param[]
     */
    public function getParams();

    /**
     * @return bool
     */
    public function isUriValid($uri);

    /**
     * @return bool
     */
    public function isParamsValid($paramValues);

    /**
     * @param $paramValues
     *
     * @return string
     */
    public function genUrlByParams($paramValues);
}
