<?php
include_once '/home/giveup/public_html/cpfm/Spam******/Bootstrap.php';

$config = array(
	'scans'=> array(
		array(
			'class' => 'SpamLib_Scan_Keyword',
			'options' => array(
				'overwrite'=>true,
				'keywords' => array('test', 'testing')
			)
		),
		array (
			'class' => 'SpamLib_Scan_Bayesian',
			'options' => array(
				'forceTrainingMode' => true,
				'hamCorpus' => new SpamLib_Scan_Bayesian_Corpus(),
				'spamCorpus' => new SpamLib_Scan_Bayesian_Corpus(),
			)
		)
	),
);

$engine = new SpamLib_Engine_Vbulletin4($config);
$engine->setPost($this);
if ($engine->run()) {
	//take anti spam action.
}

echo $score;

