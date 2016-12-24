<?php
declare(strict_types = 1);
namespace Klapuch\Storage\TestCase;

use Klapuch\Storage;
use Tester;

abstract class PostgresDatabase extends Mockery {
	/** @var Storage\Database */
	protected $database;

	protected function setUp() {
		parent::setUp();
		Tester\Environment::lock('postgres_database', __DIR__ . '/../temporary');
		$this->database = $this->connection();
	}

	private function connection(): Storage\Database {
		$credentials = parse_ini_file(__DIR__ . '/.database.ini', true);
		$this->database = new Storage\PDODatabase(
			$credentials['POSTGRES']['dsn'],
			$credentials['POSTGRES']['user'],
			$credentials['POSTGRES']['password']
		);
		return $this->database;
	}
}