<?php
class SpamLib_Scan_Bayesian_Corpus
{
	protected $_corpus = array();
	protected $_corpusLimit = 3;
	protected $_corpusSize = 0;
	
	public static function tokenise($text)
	{
		$filter = new Zend_Filter_Alnum(true);
		$text = $filter->filter($text);
	
		$tokens = explode(' ', $text);
	
		return $tokens;
	}	
	
	public function getCorpus()
	{
		return $this->_corpus;
	}
	
	public function setCorpus(array $data)
	{
		$this->_corpus = $data;
		$this->_corpusSize = array_sum($data);
		return $this;
	}
	
	public function getCorpusSize()
	{
		return $this->_corpusSize;
	}
	
	public function getCorpusLimit()
	{
		return $this->_corpusLimit;
	}
	
	public function setCorpusLimit($limit)
	{
		$this->_corpusLimit = $limit;
		return $this;
	}
	
	public function getProb($token)
	{
		$corpus = $this->getCorpus();
		$corpusSize = $this->getCorpusSize();
		if (
				$corpusSize > 0 &&
				array_key_exists($token, $corpus) &&
				$corpus[$token] > $this->getCorpusLimit()
		) {
			return bcdiv($corpus[$token], $corpusSize);
		}
	
		return 0;
	}
	
	protected function _learn($text, $outcome = 'spam')
	{
		$tokens = SpamLib_Scan_Bayesian_Corpus::tokenise($text);
	
		$corpus = $this->getCorpus();
	
		foreach ($tokens AS $token) {
			if (array_key_exists($corpus, $token)) {
				$corpus[$token]++;
			} else {
				$corpus[$token] = 1;
			}
		}
	
		$this->setCorpus($corpus);
	
		return $this;
	}
	
	public function train(array $data)
	{
		foreach ($data as $post) {
			if ($post instanceof SpamLib_Post_Abstract) {
				$this->_learn($post->subject . ' ' . $post->body);
			} else {
				$this->_learn($post);
			}
		}
	}	
	
	public function setOptions(array $options) 
	{
		if (array_key_exists('corpus', $options)) {
			$this->setCorpus($options['corpus']);
			unset($options['corpus']);
		}
		
		if (array_key_exists('corpusLimit', $options)) {
			$this->setCorpusLimit($options['corpusLimit']);
			unset($options['corpusLimit']);
		}
		
		return $this;
	}
	
	public function setConfig(Zend_Config $config)
	{
		return $this->setOptions($config->toArray());
	}
}