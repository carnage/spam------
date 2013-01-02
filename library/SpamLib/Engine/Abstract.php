<?php
abstract class SpamLib_Engine_Abstract
{
	protected $_config;
	protected $_post;
	protected $_user;
	
	public function __construct($config)
	{
		if (is_array($config)) {
			$this->_config = new Zend_Config($config);
		} elseif (stripos($config, '.ini')) {
			$this->_config = new Zend_Config_Ini($config);
		} elseif (stripos($config, '.xml')) {
			$this->_config = new Zend_Config_Xml($config);
		}
		
		$this->_post = new SpamLib_Post();
		$this->_user = new SpamLib_User();
		
	}
	
	public function run()
	{
		$scanner = new SpamLib_Scanner();
		$scanner->setConfig($this->_config);
		
		$score = $scanner->scan($this->_post, $this->_user);

		if ($score > 5) {
			return true;
		}
		
		return false;
	}	
}