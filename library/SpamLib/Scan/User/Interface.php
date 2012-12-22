<?php
interface SpamLib_Scan_User_Interface extends SpamLib_Scan_Interface 
{
	public function scanUser(SpamLib_User_Abstract $user);
}