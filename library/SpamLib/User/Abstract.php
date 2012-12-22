<?php
class SpamLib_User_Abstract extends SpamLib_DataObject_Abstract
{
	protected $_validfields = array(
		'postcount',
		'username',
		'registrationdate',
		'ip'		
	);
}