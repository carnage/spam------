<?php
abstract class SpamLib_Scan_Abstract implements SpamLib_Scan_Interface
{
	public function setConfig(Zend_Config $config)
	{
		return $this->setOptions($config->toArray());
	}
	
	public function setOptions(array $options)
	{
		return $this;
	} 
}