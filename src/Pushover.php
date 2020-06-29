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
	 *
	 * @return bool
	 */
	public function validateMessageData(array $data = []): bool
	{
		$this->errors = [];

		$validation = service('validation')->reset()->setRules([
			'message'   => 'required|string',
			'url'       => 'permit_empty|valid_url',
			'html'      => 'permit_empty|in_list[0,1]',
			'monospace' => 'permit_empty|in_list[0,1]',
			'timestamp' => 'permit_empty|is_natural',
			'priority'  => 'permit_empty|in_list[-2,-1,0,1,2]',
			'retry'     => 'permit_empty|is_natural|greater_than_equal_to[30]',
			'expire'    => 'permit_empty|is_natural',
			'callback'  => 'permit_empty|valid_url',
		]);

		if (! $validation->run($data))
		{
			$this->errors = $validation->getErrors();
		}

		// Only one of html/monospace may be us
		if (! empty($data['html']) && ! empty($data['monospace']))
		{
			$this->errors[] = lang('Pushover.htmlAndMonospace');
		}

		return empty($this->errors);
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

		if (! $this->validateMessageData())
		{
			if ($this->config->silent)
			{
				return null;
			}

			throw new PushoverException::forInvalidMessage();
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
		if (empty($data['user']) || empty($data['token']))
		{
			if ($this->config->silent)
			{
				return null;
			}

			throw new PushoverException::forMissingAuthentication();
		}

		if (! is_null($data))
		{
			$this->client->setForm($data, $multipart);
		}

		return $this->client->request($method, $endpoint);
	}
}
