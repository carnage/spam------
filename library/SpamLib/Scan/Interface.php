<?php
interface SpamLib_Scan_Interface
{
	public function setConfig(Zend_Config $config);
	public function setOptions(array $options);
	
	public function setPost(SpamLib_DataObject_Post $post);
	public function getPost();
	public function setUser(SpamLib_DataObject_User $user);
	public function getUser();
	
	public function scan();
	
}