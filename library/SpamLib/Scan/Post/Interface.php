<?php
interface SpamLib_Scan_Post_Interface extends SpamLib_Scan_Interface 
{
	public function scanPost(SpamLib_Post_Abstract $post);
}