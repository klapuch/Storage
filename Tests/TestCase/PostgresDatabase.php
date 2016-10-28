<?php
declare(strict_types = 1);
namespace Klapuch\Database\TestCase;

use Klapuch\Database;
use Tester;

abstract class PostgresDatabase extends Tester\TestCase {
	/** @var Database\Database */
	protected $database;

	protected function setUp() {
		parent::setUp();
		Tester\Environment::lock('postgres_database', __DIR__ . '/../temporary');
		$this->database = $this->connection();
	}

	private function connection(): Database\Database {
		$credentials = parse_ini_file(__DIR__ . '/.database.ini', true);
		$this->database = new Database\PDODatabase(
			$credentials['POSTGRES']['dsn'],
			$credentials['POSTGRES']['user'],
			$credentials['POSTGRES']['password']
		);
		return $this->database;
	}
}