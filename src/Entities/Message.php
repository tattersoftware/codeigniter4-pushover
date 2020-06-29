<?php namespace Tatter\Pushover\Entities;

use CodeIgniter\Entity;
use CodeIgniter\HTTP\ResponseInterface;

class Message extends Entity
{
	protected $table = 'pushover_message';
    protected $dates = ['timestamp', 'created_at', 'updated_at'];

    /**
     * Array of field names and the type of value to cast them as
     * when they are accessed.
     */
    protected $casts = [
        'html'      => 'bool',
        'monospace' => 'bool',
        'priority'  => 'int',
        'retry'     => '?int',
        'expire'    => '?int',
    ];

	/**
	 * Interface with the service to send this Message
	 */
	public function send()
	{
		$result = service('pushover')->sendMessage($this);
		
		// Update the Message with response data
		$this->status  = $result['status'];
		$this->request = $result['request'];
	}

	/**
	 * Format the Message ready for posting to the API
	 *
	 * @return array
	 */
	public function toPost(): array
	{
		$data = [
			'message'    => $this->castAs($this->message, 'string'),
			'title'      => $this->castAs($this->title, '?string'),
			'url'        => $this->castAs($this->url, '?string'),
			'url_title'  => $this->castAs($this->url_title, '?string'),
			'html'       => $this->castAs($this->html, '?int'),
			'monospace'  => $this->castAs($this->monospace, '?int'),
			'sound'      => $this->castAs($this->sound, '?string'),
			'device'     => $this->castAs($this->device, '?string'),
			'timestamp'  => is_null($this->timestamp) ? null : $this->timestamp->getTimestamp(),
			'priority'   => $this->castAs($this->priority, '?int'),
			'retry'      => $this->castAs($this->retry, '?int'),
			'expire'     => $this->castAs($this->expire, '?int'),
			'callback'   => $this->castAs($this->callback, '?string'),
		];

		// Remove any null values
		return array_filter($data, function($var) {
			return $var !== null;
		});
	}

	/**
	 * Validates that this Message is appropriate for the API
	 *
	 * @param array &$errors  Array to receive error messages
	 *
	 * @return bool
	 */
	public function validate(array &$errors = []): bool
	{
		$errors = [];
		$data = $this->toPost();

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
			$errors = $validation->getErrors();
		}

		// Only one of html/monospace may be us
		if (! empty($data['html']) && ! empty($data['monospace']))
		{
			$errors[] = lang('Pushover.htmlAndMonospace');
		}

		return empty($errors);
	}
}
