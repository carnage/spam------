<?php
class SpamLib_Scan_Keyword extends SpamLib_Scan_Abstract
{
	protected $_keywords = array(
		'ugg',
		'canda goose',
		'leiws vutton'
	);
	
	public function addKeyword($keyword)
	{
		$this->_keywords[] = $keyword;
		return $this;
	}
	
	public function addKeywords(array $keywords)
	{
		foreach ($keywords AS $keyword) {
			$this->addKeyword($keyword);
		}
		
		return $this;
	}
	
	public function setKeywords(array $keywords)
	{
		$this->_keywords = $keywords;
		return $this;
	}
	
	public function getKeywords()
	{
		return $this->_keywords;
	}
	
	public function setOptions(array $options)
	{
		if (array_key_exists('keywords', $options)) {
			if (!array_key_exists('overwrite', $options)) {
				$options['overwrite'] = false;
			}
			if ($options['overwrite'] == true) {
				$this->setKeywords($options['keywords']);
			} else {
				$this->addKeywords($options['keywords']);
			}
			
			unset($options['overwrite']);
			unset($options['keywords']);
		}
		
		return parent::setOptions($options);
	}
	
	public function scan()
	{
		$post = $this->getPost();
		
		//niave keyword search
		$score = 0;
		foreach($this->getKeywords() AS $keyword) {
			$score += substr_count($post->subject, $keyword) * 1.2;
			$score += substr_count($post->body, $keyword);
		}
		
		return $score;
	}
}