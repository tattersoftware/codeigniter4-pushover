<?php

use CodeIgniter\Test\CIUnitTestCase;
use Tatter\Pushover\Entities\Message;

class EntityTest extends CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->config = new \Tatter\Pushover\Config\Pushover();
	}

	public function testToPostReturnsCorrectDataTypes()
	{
		$data = [
			'message'   => 'Hello world.',
			'title'     => 'Simple',
			'timestamp' => time(),
			'priority'  => 1,
		];
		$data = array_merge($this->config->messageDefaults, $data);
		
		$message = new Message($data);
		$result  = $message->toPost();

		$this->assertNull($result['url']);
		$this->assertNull($result['device']);
		$this->assertIsInt($result['html']);
		$this->assertIsInt($result['timestamp']);
		$this->assertIsInt($result['priority']);
	}
}
