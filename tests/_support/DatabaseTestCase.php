<?php namespace Tests\Support;

use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\Mock\MockEmail;
use Config\Services;
use Faker\Factory;

class DatabaseTestCase extends CIDatabaseTestCase
{
	/**
	 * Faker instance for generating content.
	 *
	 * @var Faker\Factory
	 */
	protected static $faker;

	/**
	 * Should the database be refreshed before each test?
	 *
	 * @var boolean
	 */
	protected $refresh = true;

	/**
	 * The namespace(s) to help us find the migration classes.
	 * Empty is equivalent to running `spark migrate -all`.
	 * Note that running "all" runs migrations in date order,
	 * but specifying namespaces runs them in namespace order (then date)
	 *
	 * @var string|array|null
	 */
    protected $namespace = 'Tatter\Pushover';

	/**
	 * Our configuration
	 *
	 * @var CodeIgniter\Config\BaseConfig
	 */
	protected $config;

	/**
	 * Path to a file for attachments.
	 *
	 * @var string
	 */
	protected $file = SUPPORTPATH . 'cat.jpg';

    /**
     * Initializes the Test Helper.
     */
    public static function setUpBeforeClass(): void
    {
    	helper('test');
		self::$faker = Factory::create();
    }

	public function setUp(): void
	{
		parent::setUp();

		$this->config = new \Tatter\Pushover\Config\Pushover();
	}
}
