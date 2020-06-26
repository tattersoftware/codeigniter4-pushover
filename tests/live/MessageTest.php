<?php

use Tests\Support\DatabaseTestCase;
use Tatter\Pushover\Entities\Message;

class MessageTest extends DatabaseTestCase
{
	public function testSendSends()
	{
		$data = [
			'message'   => 'Hello world.',
			'title'     => 'Simple',
			'timestamp' => time(),
			'priority'  => 1,
		];
		$data = array_merge($this->config->messageDefaults, $data);
		
		$message = new Message($data);

		$response = $message->send();
		d($response);
	}
}
