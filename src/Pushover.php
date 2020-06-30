<?php namespace Tatter\Pushover;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;
use Tatter\Pushover\Exceptions\PushoverException;
use Tatter\Pushover\Entities\Message;

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
	 * @return ResponseInterface|null
	 */
	public function sendMessage(Message $message): ?ResponseInterface
	{
		$data = $message->toPost();
		
		if ($message->attachment)
		{
			$data['attachment'] = new \CURLFile($message->attachment);
		}

		if (! $message->validate($this->errors))
		{
			if ($this->config->silent)
			{
				return null;
			}

			throw PushoverException::forInvalidMessage();
		}

		// Add required auth info
		$auth = $this->auth(['user', 'token']);
		if (empty($auth))
		{
			return null;
		}

		$data = array_merge($data, $auth);

		// Check the throttle
		$this->throttle();

		return $this->send('post', 'messages.json', $data, (bool) $message->attachment);
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
	 * @return array|null
	 */
	protected function auth(array $fields = ['user', 'token']): ?array
	{
		$return = [];

		foreach ($fields as $field)
		{
			if (empty($this->config->$field))
			{
				if ($this->config->silent)
				{
					return null;
				}

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
	 * @param bool $multipart  Whether ot send form data with file support
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
}
