<?php namespace Tatter\Pushover;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;
use Tatter\Pushover\Entities\Message;

/**
 * Class Pushover
 *
 * Wrapper for the Pushover API (pushover.net)
 */
class Pushover
{
	/**
	 * Our configuration instance.
	 *
	 * @var \Tatter\Pushover\Config\Pushover
	 */
	protected $config;

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
	 * Validates the message data
	 *
	 * @param array $data  Array of values prepped for the API
	 * @param array &$errors  Variable to plave error messages in on failure
	 *
	 * @return bool
	 */
	public function validateMessage(array $data = [], array &$errors = []): bool
	{
		$validation = service('validation')->setRules([
			'message'   => 'required|string',
			'url'       => 'valid_url',
			'html'      => 'in_list[0,1]',
			'monospace' => 'in_list[0,1]',
			'timestamp' => 'is_natural',
			'priority'  => 'in_list[-2,-1,0,1,2]',
			'retry'     => 'is_natural|greater_than_equal_to[30]',
			'expire'    => 'is_natural',
			'callback'  => 'valid_url',
		]);

		if (! $validation->run($data))
		{
			$errors = array_merge($errors, $validation->getErrors());
		}
		
		return empty($errors);
	}

	/**
	 * Send a Message
	 *
	 * @param Message $message  The Message to send
	 *
	 * @return string
	 */
	public function sendMessage(Message $message): ResponseInterface
	{
		$data = $message->toPost();
		
		if ($message->attachment)
		{
			$data['attachment'] = new \CURLFile($message->attachment);
		}
		
		// Add required auth info
		$data['user']  = $this->config->user;
		$data['token'] = $this->config->token;

		return $this->send('post', 'messages.json', $data, (bool) $message->attachment);
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
