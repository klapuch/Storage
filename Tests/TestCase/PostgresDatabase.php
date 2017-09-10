<?php
declare(strict_types = 1);
namespace Klapuch\Storage\TestCase;

use Klapuch\Storage;
use Tester;

abstract class PostgresDatabase extends Mockery {
	/**
	 * @var \PDO
	 */
	protected $database;

	protected function setUp() {
		parent::setUp();
		Tester\Environment::lock('postgres', __DIR__ . '/../Temporary');
		$credentials = parse_ini_file(__DIR__ . '/../Configuration/.config.local.ini', true);
		$this->database = $this->connection($credentials);
		$this->database->exec('TRUNCATE test');
	}

	private function connection(array $credentials): \PDO {
		return new Storage\SafePDO(
			$credentials['POSTGRES']['dsn'],
			$credentials['POSTGRES']['user'],
			$credentials['POSTGRES']['password']
		);
	}
}