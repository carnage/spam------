<?php
class SpamLib_Scan_Bayesian extends SpamLib_Scan_Abstract implements SpamLib_Scan_Post_Interface
{
	protected $_hamCorpus = array();
	protected $_spamCorpus = array();
	protected $_spamCorpusMinSize = 0;
	protected $_hamCorpusMinSize = 0;
	protected $_corpusMinSize = 0;
	protected $_forceTrainingMode = false;
	
	
	/**
	 * Should return probability of word appearing in spam / prob of word in a post (eg notspam + spam probs)
	 * 
	 * @param string $token
	 * @return number
	 */
	protected function _getTokenScore($token) 
	{
		$hamScore = $this->getHamCorpus()->getProb($token);
		$spamScore = $this->getSpamCorpus()->getProb($token);
		
		if ($spamScore == 0 && $hamScore == 0) {
			return 0.4; //not seen before.
		} elseif ($spamScore == 0) {
			return 0.01; //only ever seen in ham
		} elseif ($hamScore == 0) {
			return 0.99; //only ever seen in spam
		}
		return bcdiv($spamScore, bcadd($spamScore, $hamScore));
		
	}
	
	public function isTrainingMode()
	{
		return $this->getForceTrainingMode() || (
			$this->getSpamCorpus()->getCorpusSize() < $this->_spamCorpusMinSize ||
			$this->getHamCorpus()->getCorpusSize() < $this->_hamCorpusMinSize ||
			$this->getHamCorpus()->getCorpusSize() + $this->getSpamCorpus()->getCorpusSize() < $this->_corpusMinSize
		);
	}
	
	public function getHamCorpus()
	{
		return $this->_hamCorpus;
	}
	
	public function getSpamCorpus()
	{
		return $this->_spamCorpus;
	}
	
	public function setHamCorpus(SpamLib_Scan_Bayesian_Corpus $corpus)
	{
		$this->_hamCorpus = $corpus;
		return $this;
	}
	
	public function setSpamCorpus(SpamLib_Scan_Bayesian_Corpus $corpus)
	{
		$this->_spamCorpus = $corpus;
		return $this;
	}
	
	public function setCorpusMinSize($size)
	{
		$this->_corpusMinSize = $size;
		return $this;
	}
	
	public function setHamCorpusMinSize($size)
	{
		$this->_hamCorpusMinSize = $size;
		return $this;
	}

	public function setSpamCorpusMinSize($size)
	{
		$this->_spamCorpusMinSize = $size;
		return $this;
	}

	public function getCorpusMinSize()
	{
		return $this->_corpusMinSize;
	}

	public function getSpamCorpusMinSize()
	{
		return $this->_spamCorpusMinSize;
	}

	public function getHamCorpusMinSize()
	{
		return $this->_hamCorpusMinSize;
	}	
	
	public function getForceTrainingMode()
	{
		return $this->_forceTrainingMode;
	}
	
	public function setForceTrainingMode($mode = true)
	{
		$this->_forceTrainingMode = $mode;
		return $this;
	}
	
	public function scanPost(SpamLib_Post_Abstract $post)
	{
		$text = $post->subject . ' ' . $post->body;
		$tokens = SpamLib_Scan_Bayesian_Corpus::tokenise($text);
		
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
		
		if ($this->isTrainingMode()) {
			$this->_log($text, $postScore);
			//in training mode always return 0
			return 0;
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
			if (is_array($options['spamCorpus'])) {
				if (array_key_exists('class', $options['spamCorpus'])) {
					$spamCorpus = new $options['spamCorpus']['class']();
				} else {
					throw new Exception ('invalid options');
				}
				if (array_key_exists('options', $options['spamCorpus'])) {
					$spamCorpus->setOptions($options['spamCorpus']['options']);
				}
				
				$this->setSpamCorpus($spamCorpus);
				unset($spamCorpus);
			} else {
				$this->setSpamCorpus($options['spamCorpus']);
			}
			
			unset($options['spamCorpus']);
		}
		if (array_key_exists('hamCorpus', $options)) {
			if (is_array($options['hamCorpus'])) {
				if (array_key_exists('class', $options['hamCorpus'])) {
					$hamCorpus = new $options['hamCorpus']['class']();
				} else {
					throw new Exception ('invalid options');
				}
				if (array_key_exists('options', $options['hamCorpus'])) {
					$hamCorpus->setOptions($options['hamCorpus']['options']);
				}
				
				$this->setHamCorpus($hamCorpus);
				unset($hamCorpus);
			} else {
				$this->setHamCorpus($options['hamCorpus']);
			}
			unset($options['hamCorpus']);
		}
		
		if (array_key_exists('spamCorpusMinSize', $options)) {
			$this->setSpamCorpusMinSize($options['spamCorpusMinSize']);
			unset($optioins['spamCorpusMinSize']);
		}

		if (array_key_exists('hamCorpusMinSize', $options)) {
			$this->setHamCorpusMinSize($options['hamCorpusMinSize']);
			unset($optioins['hamCorpusMinSize']);
		}

		if (array_key_exists('corpusMinSize', $options)) {
			$this->setCorpusMinSize($options['corpusMinSize']);
			unset($optioins['corpusMinSize']);
		}		
		
		if (array_key_exists('forceTrainingMode', $options)) {
			$this->setForceTrainingMode($options['forceTrainingMode']);
			unset($options['forceTrainingMode']);
		}
		
		return parent::setOptions($options);
	}
	
	protected function _log($text, $score) 
	{
		$h = fopen(BASE_PATH . DIRECTORY_SEPARATOR . 'log.xml', 'a');
		fwrite($h, '<entry><text>' . $text . '</text><score>' . $score . '</score></entry>');
		fclose($h);
	}
}