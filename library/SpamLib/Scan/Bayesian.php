<?php
class SpamLib_Scan_Bayesian extends SpamLib_Scan_Abstract implements SpamLib_Scan_Post_Interface
{
	protected $_corpusLimit = 3;
	protected $_hamCorpus = array();
	protected $_hamCorpusSize = 0;
	protected $_spamCorpus = array();
	protected $_spamCorpusSize = 0;
	
	protected function _tokenise($text)
	{
		$filter = new Zend_Filter_Alnum(true);
		$text = $filter->filter($text);
	
		$tokens = explode(' ', $text);
	
		return $tokens;
	}
		
	/**
	 * Should return probability of word appearing in spam / prob of word in a post (eg notspam + spam probs)
	 * 
	 * @param string $token
	 * @return number
	 */
	protected function _getTokenScore($token) 
	{
		$hamScore = $this->_getHamProb($token);
		$spamScore = $this->_getSpamProb($token);
		
		if ($spamScore == 0 && $hamScore == 0) {
			return 0.4; //not seen before.
		} elseif ($spamScore == 0) {
			return 0.01; //only ever seen in ham
		} elseif ($hamScore == 0) {
			return 0.99; //only ever seen in spam
		}
		return bcdiv($spamScore, bcadd($spamScore, $hamScore));
		
	}
	
	protected function _getHamProb($token)
	{
		if (
				$this->_hamCorpusSize > 0 && 
				array_key_exists($token, $this->_hamCorpus) &&
				$this->_hamCorpus[$token] > $this->_corpusLimit
			) {
			return bcdiv($this->_hamCorpus[$token], $this->_hamCorpusSize);
		}
		
		return 0;
	}
	
	protected function _getSpamProb($token)
	{
		if (
				$this->_spamCorpusSize > 0 && 
				array_key_exists($token, $this->_spamCorpus) &&
				$this->_spamCorpus[$token] > $this->_corpusLimit
			) {
			return bcdiv($this->_spamCorpus[$token], $this->_spamCorpusSize);
		}
	
		return 0;
	}	
	
	public function getHamCorpus()
	{
		return $this->_hamCorpus;
	}
	
	public function getSpamCorpus()
	{
		return $this->_spamCorpus;
	}
	
	public function setHamCorpus(array $corpus)
	{
		$this->_hamCorpus = $corpus;
		$this->_hamCorpusSize = array_sum($corpus);
		return $this;
	}
	
	public function setSpamCorpus(array $corpus)
	{
		$this->_spamCorpus = $corpus;
		$this->_spamCorpusSize = array_sum($corpus);
		return $this;
	}
	
	public function scanPost(SpamLib_Post_Abstract $post)
	{
		$text = $post->subject . ' ' . $post->body;
		$tokens = $this->_tokenise($text);
		
		$interestingTokens = array();
		$interestThreashold = 0;
		
		foreach ($tokens AS $token) {
			$score = $this->_getTokenScore($token);
			$scoreWeight = abs($score - 0.5);
			if ($scoreWeight > $interestThreashold) {
				if (count($interestingTokens) < 16) {
					//add token to the list
					$interestingTokens[] = array('token'=>$token, 'score'=>$score, 'scoreWeight'=>$scoreWeight);
				} else {
					//replace the least interesting token in our list with the new one
					$leastInterestingValue = 1;
					$leastInterestingKey = 0;
					foreach ($interestingTokens AS $key => $tokenInfo) {
						if ($tokenInfo['scoreWeight'] < $leastInterestingValue) {
							$leastInterestingValue = $tokenInfo['scoreWeight'];
							$leastInterestingKey = $key;
						}
					}
					
					$interestThreashold = $leastInterestingValue;
					$interestingTokens[$key] = array('token'=>$token, 'score'=>$score, 'scoreWeight'=>$scoreWeight);
				}
			}
		}
		
		$postScore = 0;
		
		if (count($interestingTokens) > 3) {
			$total = 0;
			$antiTotal = 0;
			foreach($interestingTokens AS $token) {
				$total = bcadd($total, $token['score']);
				$antiTotal = bcadd($antiTotal, bcsub(1, $token['score']));
			}
			
			$postScore = bcdiv($total, bcadd($total, $antiTotal));
		}
		
		if (bccomp(0.9, $postScore) < 1) {
			//postscore > 0.9
			return 10;
		}
		
		return 0;
	}
	
	public function setOptions(array $options)
	{
		if (array_key_exists('spamCorpus', $options)) {
			$this->setSpamCorpus($options['spamCorpus']);
			unset($options['spamCorpus']);
		}
		if (array_key_exists('hamCorpus', $options)) {
			$this->setSpamCorpus($options['hamCorpus']);
			unset($options['hamCorpus']);
		}
		
		return parent::setOptions($options);
	}
	
	protected function _learn($text, $outcome = 'spam')
	{
		$tokens = $this->_tokenise($text);

		if ($outcome == 'spam') {
			$corpus = $this->getSpamCorpus();
		} else {
			$corpus = $this->getHamCorpus();
		}
		
		foreach ($tokens AS $token) {
			if (array_key_exists($corpus, $token)) {
				$corpus[$token]++;
			} else {
				$corpus[$token] = 1;
			}
		}
		
		if ($outcome == 'spam') {
			$this->setSpamCorpus($corpus);
		} else {
			$this->setHamCorpus($corpus);
		}	
		
		return $this;	
	}
	
	public function train(array $spam, array $ham) 
	{
		foreach ($spam as $post) {
			if ($post instanceof SpamLib_Post_Abstract) {
				$this->_learn($post->subject . ' ' . $post->body, 'spam');
			} else {
				$this->_learn($post, 'spam');
			}
		}
		
		foreach ($ham as $post) {
			if ($post instanceof SpamLib_Post_Abstract) {
				$this->_learn($post->subject . ' ' . $post->body, 'ham');
			} else {
				$this->_learn($post, 'ham');
			}
		}		
	}
}