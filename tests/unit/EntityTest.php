<?php

use CodeIgniter\Test\CIUnitTestCase;
use Tatter\Pushover\Entities\Message;

class EntityTest extends CIUnitTestCase
{
	use Tests\Support\MockPushoverTrait;

	public function setUp(): void
	{
		parent::setUp();

		$this->mockPushover();
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

		$this->assertArrayNotHasKey('url', $result);
		$this->assertArrayNotHasKey('device', $result);
		$this->assertIsInt($result['html']);
		$this->assertIsInt($result['timestamp']);
		$this->assertIsInt($result['priority']);
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
				['message' => 'Hello world', 'url' => 'not a URL'],
				false,
			],
			[
				['message' => 'Hello world', 'url' => 'https://example.com'],
				true,
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
				['message' => 'Hello world', 'priority' => -2],
				true,
			],
			[
				['message' => 'Hello world', 'priority' => -5],
				false,
			],
			[
				['message' => 'Hello world', 'callback' => 'https://example.com'],
				true,
			],
			[
				['message' => 'Hello world', 'callback' => 'not a URL'],
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
		$result  = $message->validate();

		$this->assertEquals($expected, $result);
	}
}
