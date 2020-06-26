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
        'html'      => 'boolean',
        'monospace' => 'boolean',
        'priority'  => 'int',
        'retry'     => 'int',
        'expire'    => 'int',
    ];

	/**
	 * Interface with the service to send this Message
	 *
	 * @return ResponseInterface  Response from the CURLRequest
	 */
	public function send(): ResponseInterface
	{
		$result = service('pushover')->sendMessage($this);

		return $result;
	}

	/**
	 * Format the Message ready for posting to the API
	 *
	 * @return array
	 */
	public function toPost(): array
	{
		return [
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
	}
}
