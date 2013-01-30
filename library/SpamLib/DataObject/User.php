<?php
class SpamLib_DataObject_User extends SpamLib_DataObject_Abstract
{
	protected $_validfields = array(
		'postcount',
		'username',
		'registrationdate',
		'ip'		
	);
}