<?php
include_once '/home/giveup/public_html/cpfm/Spam******/Bootstrap.php';

$post = new SpamLib_Post();
$user = new SpamLib_User();

$post->subject = $this->post['title'];
$post->body = $this->post['pagetext'];

$user->username = $this->post['username'];

//$scannerConfig = new Zend_Config_Xml('standard.xml');

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

$scanner = new SpamLib_Scanner();
$scanner->setOptions($config);

$score = $scanner->scan($post, $user);

echo $score;

