<?php
abstract class SpamLib_Service_Scanner_Abstract
{
	protected $_post;
	protected $_user;
	protected $_class;
	protected $_classOptions;	
	
	public function __construct($config)
	{
		$zendConfig = null;
		
		if (is_array($config)) {
			$zendConfig = new Zend_Config($config);
		} elseif (stripos($config, '.ini')) {
			$zendConfig = new Zend_Config_Ini($config);
		} elseif (stripos($config, '.xml')) {
			$zendConfig = new Zend_Config_Xml($config);
		}
		
		if ($zendConfig instanceof Zend_Config) {
			$this->setConfig($zendConfig);
		}
		
		$this->_post = new SpamLib_DataObject_Post();
		$this->_user = new SpamLib_DataObject_User();
		
	}
	
	public function run()
	{
		$scannerClass = $this->getClass();
		if (empty($scannerClass)) {
			throw new Exception('No scanner class defined');
		}
		
		$scanner = new $scannerClass();
		
		$scannerOptions = $this->getClassOptions();
		if (!is_null($scannerOptions)) {
			$scanner->setOptions($scannerOptions);
		}
		
		$scanner->setPost($this->_post);
		$scanner->setUser($this->_user);
		$score = $scanner->scan($this->_post, $this->_user);
		// allow child class to take over at this point to implement spam counter measures
		if ($score > 5) {
			return true;
		}
		
		return false;
	}	
	
	public function getPost()
	{
		return $this->_post;
	}
	
	public function getUser()
	{
		return $this->_user;
	}
	
	public function setConfig(Zend_Config $config)
	{
		return $this->setOptions($config->toArray());
	}
	
	public function setOptions(array $options)
	{
		if (array_key_exists('class', $options)) {
			$this->setClass($options['class']);
			unset($options['class']);
		}
		
		if (array_key_exists('options', $options)) {
			$this->setClassOptions($options['options']);
			unset($options['options']);
		}
		
		return $this;
	}
	
	public function getClass()
	{
		return $this->_class;
	}
	
	public function setClass($class)
	{
		$this->_class = $class;
		return $this;
	}
	
	public function getClassOptions()
	{
		return $this->_classOptions;
	}
	
	public function setClassOptions(array $options)
	{
		$this->_classOptions = $options;
		return $this;
	}
}