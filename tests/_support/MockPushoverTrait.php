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

		$this->config->throttle = 3;
		$this->config->user     = 'abcdef';
		$this->config->token    = 'xyz123';

		$this->client = Services::curlrequest([
			'base_uri'    => $this->config->baseUrl,
			'http_errors' => false,
		], null, null, false);

		$this->pushover = new MockPushover($this->config, $this->client);

		// Reset the throttle
		MockPushover::setThrottle();
	}
}
