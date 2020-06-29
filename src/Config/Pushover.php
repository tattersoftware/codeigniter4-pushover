<?php namespace Tatter\Pushover\Config;

use CodeIgniter\Config\BaseConfig;

class Pushover extends BaseConfig
{
	/**
	 * Debug mode
	 *
	 * @var boolean
	 */
	public $debug = (ENVIRONMENT !== 'production');

	/**
	 * Whether Messages should be logged in the database.
	 *
	 * @var boolean
	 */
	public $database = true;

	/**
	 * Whether failures should exit quietly instead of throwing exceptions
	 *
	 * @var boolean
	 */
	public $silent = true;

	/**
	 * Base URL for cURL requests
	 *
	 * @var string
	 */
	public $baseUrl = 'https://api.pushover.net/1/';

	/**
	 * Seconds to delay between metered calls
	 *
	 * @var int
	 */
	public $throttle = 5;

	/**
	 * User secret key
	 *
	 * @var string
	 */
	public $user = '';

	/**
	 * Application-specific API token
	 *
	 * @var string
	 */
	public $token = '';

	/**
	 * Default values for Messages
	 * Reference: https://pushover.net/api#messages
	 *
	 * @var array
	 */
	public $messageDefaults = [
		'message'    => '',
		'title'      => null,
		'url'        => null,
		'url_title'  => null,
		'html'       => 0,
		'monospace'  => 0,
		'sound'      => null,
		'attachment' => null,
		'device'     => null,
		'timestamp'  => null,
		'priority'   => 0,
		'retry'      => null,
		'expire'     => null,
		'callback'   => null,
	];
}
