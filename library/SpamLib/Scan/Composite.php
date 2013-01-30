<?php
class SpamLib_Scan_Composite extends SpamLib_Scan_Abstract
{
	protected $_scans;
	
	public function scan()
	{
		$score = 0;
		$scans = $this->getScans();
		
		if (is_array($this->getScans())) {
			$user = $this->getUser();
			$post = $this->getPost();
			foreach ($this->getScans() AS $scan) {
				if (!is_null($user)) {
					$scan->setUser($user);
				}
				if (!is_null($post)) {
					$scan->setPost($post);
				}
				
				$score += $scan->scan();
			}
		}
		
		return $score;
	}
	
	public function addScan(SpamLib_Scan_Interface $scan)
	{
		$this->_scans[] = $scan;
		return $this;
	}
	
	public function addScans(array $scans) 
	{
		foreach ($scans AS $scan) {
			$this->addScan($scan);
		}
		
		return $this;
	}
	
	public function setScans(array $scans)
	{
		$this->_scans = $scans;
		return $this;
	}
	
	public function getScans()
	{
		return $this->_scans;
	}
	
	public function setConfig(Zend_Config $config)
	{
		return $this->setOptions($config->toArray());
	}
	
	public function setOptions(array $options)
	{
		if (array_key_exists('scans', $options)) {
			foreach ($options['scans'] AS $scan) {
				if (is_array($scan)) {
					$scanObject = new $scan['class']();
					$scanObject->setOptions($scan['options']);
				} else {
					$scanObject = new $scan();
				}
				
				$this->addScan($scanObject);
			}
		}
		
		return $this;
	}
}