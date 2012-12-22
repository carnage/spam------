<?php
interface SpamLib_Scan_Interface
{
	public function setConfig(Zend_Config $config);
	public function setOptions(array $options);
	
}