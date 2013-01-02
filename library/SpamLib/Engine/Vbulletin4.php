<?php
class SpamLib_Engine_Vbulletin4 extends SpamLib_Engine_Abstract
{
	public function setPost($postData)
	{
		//function takes ($this) from the context of the postdata_presave hook and sets up the scanner
		$post->subject = $postData->post['title'];
		$post->body = $postData->post['pagetext'];
		
		$user->username = $postData->post['username'];		
		
	}
}