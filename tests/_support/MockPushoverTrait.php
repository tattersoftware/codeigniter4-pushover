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

		$this->config->throttle = 2;
		$this->config->user     = 'abcdef';
		$this->config->token    = 'xyz123';

		$client = Services::curlrequest(['base_uri' => $this->config->baseUrl]);

		$this->pushover = new MockPushover($this->config, $client);

		// Reset the throttle
		MockPushover::setThrottle();
	}
}
