<?php
interface Miaox_Acs_Adapter_Interface
{
	public function allowResource();
	public function denyResource();
	public function getPermission();
	public function getUser();
}