<?php
class SpamLib_DataObject_Abstract
{
	protected $_data = array();
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
		if (in_array($name, $this->_validfields)) {
			$this->_data[$name] = $value;
		}
		
		return $this;
	}
}