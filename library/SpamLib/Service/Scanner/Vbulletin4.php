<?php
class SpamLib_Service_Scanner_Vbulletin4 extends SpamLib_Service_Scanner_Abstract
{
	public function setPostData($postData)
	{
		$post = $this->getPost();
		$user = $this->getUser();
		
		//function takes ($this) from the context of the postdata_presave hook and sets up the scanner
		$post->subject = $postData->post['title'];
		$post->body = $postData->post['pagetext'];
		
		$user->username = $postData->post['username'];		
		
	}
}