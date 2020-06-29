<?php namespace Tests\Support;

use Config\Services;
use Tatter\Pushover\Test\MockPushover;

trait MockPushoverTrait
{
	/**
	 * Mocked instance of the service
	 *
	 * @var MockPushover
	 */
	protected $pushover;

	public function mockPushover()
	{
		$this->config = new \Tatter\Pushover\Config\Pushover();
		$this->config->silent = false;

		$client = Services::curlrequest(['base_uri' => $this->config->baseUrl]);

		$this->pushover = new MockPushover($this->config, $client);
	}
}
