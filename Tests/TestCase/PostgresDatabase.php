<?php
declare(strict_types = 1);
namespace Klapuch\Storage\TestCase;

use Klapuch\Storage;
use Tester;

abstract class PostgresDatabase extends Mockery {
	protected $database;

	protected function setUp() {
		parent::setUp();
		Tester\Environment::lock('postgres_database', __DIR__ . '/../temporary');
		$credentials = parse_ini_file(__DIR__ . '/.database.ini', true);
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