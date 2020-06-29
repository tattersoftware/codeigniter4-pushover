<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tatter\Pushover\Entities\Message;
use Tatter\Pushover\Test\MockPushover;

class LibraryTest extends CIUnitTestCase
{
	use Tests\Support\MockPushoverTrait;

	public function setUp(): void
	{
		parent::setUp();

		$this->mockPushover();
	}

	public function messageDataProvider()
	{
		return [
			[
				['message' => ''],
				false,
			],
			[
				['message' => 'Hello world'],
				true,
			],
			[
				['message' => 'Hello world', 'url' => 'https://example.com'],
				true,
			],
			[
				['message' => 'Hello world', 'url' => 'foobar'],
				false,
			],
			[
				['message' => 'Hello world', 'html' => 1, 'monospace' => 0],
				true,
			],
			[
				['message' => 'Hello world', 'html' => 0, 'monospace' => 1],
				true,
			],
			[
				['message' => 'Hello world', 'html' => 1, 'monospace' => 1],
				false,
			],
			[
				['message' => 'Hello world', 'timestamp' => time()],
				true,
			],
			[
				['message' => 'Hello world', 'timestamp' => -5000],
				false,
			],
			[
				['message' => 'Hello world', 'timestamp' => date('Y-m-d H:i:s')],
				false,
			],
			[
				['message' => 'Hello world', 'priority' => -2],
				true,
			],
			[
				['message' => 'Hello world', 'priority' => -5],
				false,
			],
			[
				['message' => 'Hello world', 'priority' => 'booger'],
				false,
			],
			[
				['message' => 'Hello world', 'callback' => 'https://example.com'],
				true,
			],
			[
				['message' => 'Hello world', 'callback' => 'foobar'],
				false,
			],
		];
	}

	/**
	 * @dataProvider messageDataProvider
	 */
	public function testValidateMessageData(array $data, bool $expected)
	{
		$data = array_merge($this->config->messageDefaults, $data);
		
		$message = new Message($data);
		$result  = $this->pushover->validateMessageData($message->toPost());

		$this->assertEquals($expected, $result);
	}
}
