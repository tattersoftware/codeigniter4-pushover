<?php namespace Tatter\Pushover\Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\HTTP\CURLRequest;
use Tatter\Pushover\Pushover;

class Services extends \Config\Services
{
	/**
	 * Returns an authenticated Factory for the Firebase SDK
	 *
	 * @param BaseConfig  $config
	 * @param boolean  $getShared
	 *
	 * @return \Tatter\Pushover\Pushover
	 */
	public static function pushover(BaseConfig $config = null, CURLRequest $client = null, bool $getShared = true): Pushover
	{
		if ($getShared)
		{
			return static::getSharedInstance('pushover', $config, $client);
		}

		if (is_null($config))
		{
			$config = config('Pushover');
		}

		if (is_null($client))
		{
			$client = \Config\Services::curlrequest(['base_uri' => $config->baseUrl]);
		}

		return new Pushover($config, $client);
	}
}
