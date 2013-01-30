<?php
abstract class SpamLib_Scan_Abstract implements SpamLib_Scan_Interface
{
	protected $_post;
	protected $_user;
	
	public function setConfig(Zend_Config $config)
	{
		return $this->setOptions($config->toArray());
	}
	
	public function setOptions(array $options)
	{
		return $this;
	}

	public function setPost(SpamLib_DataObject_Post $post)
	{
		$this->_post = $post;
		return $this;
	}
	
	public function getPost()
	{
		return $this->_post;
	}
	
	public function setUser(SpamLib_DataObject_User $user)
	{
		$this->_user = $user;
		return $this;
	}
	
	public function getUser()
	{
		return $this->_user;
	}
}