<?php namespace Tatter\Pushover;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;
use Tatter\Pushover\Exceptions\PushoverException;
use Tatter\Pushover\Entities\Message;
use Tatter\Pushover\Models\MessageModel;

/**
 * Class Pushover
 *
 * Wrapper for the Pushover API (pushover.net)
 */
class Pushover
{
	/**
	 * Timestamp for next allowed send.
	 *
	 * @var int|null
	 */
	protected static $throttle;

	/**
	 * Our configuration instance.
	 *
	 * @var \Tatter\Pushover\Config\Pushover
	 */
	protected $config;

	/**
	 * Error messages from the last call
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Store the configuration and initialize the library.
	 *
	 * @param BaseConfig  $config
	 * @param CURLRequest  $client
	 */
	public function __construct(BaseConfig $config, CURLRequest $client)
	{
		$this->config = $config;
		$this->client = $client;
	}

	//--------------------------------------------------------------------
	// MESSAGES
	//--------------------------------------------------------------------

	/**
	 * Create a new Message from the supplied $data
	 *
	 * @param array $data  Array of values for Message and API (see Config\Pushover::$messageDefaults)
	 *
	 * @return string
	 */
	public function message(array $data = []): Message
	{
		return new Message(array_merge($this->config->messageDefaults, $data));
	}

	/**
	 * Send a Message
	 *
	 * @param Message $message  The Message to send
	 *
	 * @return array  The result
	 */
	public function sendMessage(Message &$message): array
	{
		$data = $message->toPost();
		
		if ($message->attachment)
		{
			$data['attachment'] = new \CURLFile($message->attachment, mime_content_type($message->attachment));
		}

		if (! $message->validate($this->errors))
		{
			throw PushoverException::forInvalidMessage();
		}

		// Add required auth info
		$data = array_merge($data, $this->getAuthValues(['user', 'token']));

		// Check the throttle
		$this->throttle();

		// Make the API call
		$response = $this->send('post', 'messages.json', $data, (bool) $message->attachment);
		
		// Parse the results
		$result = $this->parseResponse($response);

		// Update the Message with response data
		$message->status  = $result['status'];
		$message->request = $result['request'];

		// Check if we need to store a copy in the database
		if ($this->config->database)
		{
			model(MessageModel::class)->insert($message);
		}
		
		return $result;
	}

	//--------------------------------------------------------------------
	// UTILITY
	//--------------------------------------------------------------------

	/**
	 * Get and clear any error messsages
	 *
	 * @return array  Any error messages from the last call
	 */
	public function getErrors(): array
	{
		$errors       = $this->errors;
		$this->errors = [];

		return $errors;
	}

	/**
	 * Return array of auth data
	 *
	 * @param $fields  The requested auth fields
	 *
	 * @return array
	 */
	protected function getAuthValues(array $fields = ['user', 'token']): array
	{
		$return = [];

		foreach ($fields as $field)
		{
			if (empty($this->config->$field))
			{
				throw PushoverException::forMissingAuthField($field);
			}

			$return[$field] = $this->config->$field;
		}
		
		return $return;
	}

	/**
	 * Check and set the throttle
	 *
	 * @param $fields  The requested auth fields
	 */
	protected function throttle()
	{
		// Check the throttle
		if (is_int(self::$throttle) && time() < self::$throttle)
		{
			// Sleep up until the time specified by $throttle
			sleep(min(10, self::$throttle - time()));
		}

		// Set the throttle to the new time
		self::$throttle = time() + $this->config->throttle;
	}

	/**
	 * Send an API request
	 *
	 * @param string $method  HTTP method to use
	 * @param string $endpoint  API endpoint over the base URL
	 * @param array|null $data  Array of post data for the API
	 * @param bool $multipart  Whether to send form data with file support
	 *
	 * @return ResponseInterface
	 */
	public function send(string $method, string $endpoint, array $data = null, bool $multipart = false): ResponseInterface
	{
		if (! is_null($data))
		{
			$this->client->setForm($data, $multipart);
		}

		return $this->client->request($method, $endpoint);
	}

	/**
	 * Process the API response. Since the API can return content with failing
	 * HTTP response codes we process the body first.
	 *
	 * @param ResponseInterface $response  Response from the API
	 *
	 * @return array  Parsed and validated response body
	 */
	public function parseResponse(ResponseInterface $response): array
	{
		// Verify the response body
		$body = $response->getBody();
		if (empty($body))
		{
			$this->failOut(lang('Pushover.emptyResponse'));
		}

		// Decode the body
		$result = json_decode($body, true);
		if ($result === false || ! isset($result['status']))
		{
			$this->failOut(lang('Pushover.invalidResponse', [$body]));
		}

		// Harvest any errors
		if (! empty($result['errors']))
		{
			$this->errors = $result['errors'];
		}

		// Validate the result
		$validation = service('validation')->reset()->setRules([
			'status'   => 'required|is_natural',
			'request'  => 'required|alpha_dash|min_length[32]',
		]);
		if (! $validation->run($result))
		{
			$this->failOut(lang('Pushover.invalidResponse', [$body]));
		}

		// Check for failing status
		if ($result['status'] !== 1)
		{
			$this->failOut(lang('Pushover.invalidStatus', [$result['status']]));
		}

		// Handle the HTTP response code
		if ($response->getStatusCode() !== 200)
		{
			$this->failOut(lang('Pushover.invalidCode', [$response->getStatusCode()]));
		}

		return $result;
	}

	/**
	 * Fail with $errors as the exception message.
	 *
	 * @param string $error  Additional error message to add
	 *
	 * @throws PushoverException
	 */
	protected function failOut(string $error = null)
	{
		if ($error)
		{
			$this->errors[] = $error;
		}
		
		throw new PushoverException(implode(' | ', $this->errors));		
	}
}
