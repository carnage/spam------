<?php
class SpamLib_DataObject_Abstract
{
	protected $_data;
	protected $_validfields;
	
	public function __get($name)
	{
		if (!array_key_exists($name, $this->_data)) {
			return null;
		}
		return $this->_data[$name];
	}
	
	public function __set($name, $value)
	{
		if (array_key_exists($name, $this->_validfields)) {
			$this->_data[$name] = $value;
		}
		
		return $this;
	}
}