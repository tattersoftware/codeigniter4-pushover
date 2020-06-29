<?php namespace Tatter\Pushover\Test;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\Response;
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
	public $endpoints = ['messages.json'];

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

		self::$throttle = $seconds;
	}

	/**
	 * Generates a random request UUID
	 */
	protected static function generateUid()
	{
		$chunks = str_split(bin2hex(random_bytes(16)), 4);
		
		return $chunks[0] . $chunks[1] . '-' .
			$chunks[2] . '-' .
			$chunks[3] . '-' .
			$chunks[4] . '-' .
			$chunks[5] . $chunks[6]. $chunks[7];
	}

	/**
	 * Fake an API request
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
		// Create a new response
		$response = new Response(config('App'));

		// Vary response data based on desired outcome
		if (! in_array($endpoint, $this->endpoints))
		{
			$response->setStatusCode(404, 'Not Found');
			$body = [
				'status'  => 0,
				'errors' => ['resource not found'],
			];
		}
		elseif ($this->success)
		{
			$response->setStatusCode(200);
			$body = [
				'status'  => 1,
				'request' => self::generateUid()
			];
		}
		else
		{
			$response->setStatusCode(200);
			$body = [
				'status' => 0,
				'field'  => 'invalid',
				'errors' => ['field is invalid'],
			];		
		}

		$body['request'] = self::generateUid();
		
		$response->setBody(json_encode($body));
		return $response;
	}
	
}
