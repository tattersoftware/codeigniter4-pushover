<?php namespace Tatter\Pushover\Test;

use CodeIgniter\HTTP\ResponseInterface;
use Tatter\Pushover\Exceptions\PushoverException;
use Tatter\Pushover\Pushover;

/**
 * Class MockPushover
 *
 * Intercept and validate send() calls without connecting
 * to the actual API.
 */
class MockPushover extends Pushover
{
	/**
	 * Whether this send() should succeed
	 *
	 * @var bool
	 */
	public $success = true;

	/**
	 * Valid endpoints
	 *
	 * @var array
	 */
	protected $endpoints = ['messages.json'];

	/**
	 * (Re)set the throttle
	 *
	 * @param int $seconds
	 */
	public static function setThrottle(int $seconds = null)
	{
		if (! is_null($seconds))
		{
			$seconds += time();
		}

		self:$throttle = $seconds;
	}

	/**
	 * Send an API request
	 *
	 * @param string $method  HTTP method to use
	 * @param string $endpoint  API endpoint over the base URL
	 * @param array|null $data  Array of post data for the API
	 * @param bool $multipart  Whether ot send form data with file support
	 *
	 * @return ResponseInterface
	 */
	public function send(string $method, string $endpoint, array $data = null, bool $multipart = false): ResponseInterface
	{
		// Validate the endpoint
		if (! in_array($endpoint, $this->endpoints))
		{
			throw new PushoverException('Invalid endpoint: ' . $endpoint);
		}

		$response = service('response');

		/*
		{"status":1,"request":"647d2300-702c-4b38-8b2f-d56326ae460b"}

		{"user":"invalid","errors":["user identifier is invalid"], "status":0,"request":"5042853c-402d-4a18-abcb-168734a801de"}
		*/

		return $response;
	}
}
