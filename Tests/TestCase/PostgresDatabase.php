<?php
declare(strict_types = 1);
namespace Klapuch\Storage\TestCase;

use Klapuch\Storage;
use Predis;
use Tester;

abstract class PostgresDatabase extends Mockery {
	/**
	 * @var \Klapuch\Storage\MetaPDO
	 */
	protected $database;

	/** @var \Predis\Client */
	protected $redis;

	protected function setUp() {
		parent::setUp();
		Tester\Environment::lock('postgres', __DIR__ . '/../Temporary');
		$credentials = parse_ini_file(__DIR__ . '/../Configuration/.config.local.ini', true);
		$this->redis = new Predis\Client($credentials['REDIS']['uri']);
		$this->redis->flushall();
		$this->database = $this->connection($credentials);
		$this->database->exec('TRUNCATE test');
	}

	private function connection(array $credentials): Storage\MetaPDO {
		return new Storage\MetaPDO(
			new Storage\SafePDO(
				$credentials['POSTGRES']['dsn'],
				$credentials['POSTGRES']['user'],
				$credentials['POSTGRES']['password']
			),
			$this->redis
		);
	}
}