<?php
class SpamLib_Scanner
{
	protected $_scans;
	
	public function scan(SpamLib_Post_Abstract $post, SpamLib_User_Abstract $user)
	{
		$score = 0;
		$scans = $this->getScans();
		
		if (is_array($this->getScans())) {
			foreach ($this->getScans() AS $scan) {
				if ($scan instanceof SpamLib_Scan_Post_Interface) {
					$score += $scan->scanPost($post);
				}
				if ($scan instanceof SpamLib_Scan_User_Interface) {
					$score += $scan->scanUser($user);
				}
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