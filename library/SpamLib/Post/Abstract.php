<?php
class SpamLib_Post_Abstract extends SpamLib_DataObject_Abstract
{
	protected $_validfields = array(
		'subject',
		'body'
	);
}