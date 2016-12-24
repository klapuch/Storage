<?php
declare(strict_types = 1);
namespace Klapuch\Storage\TestCase;

use Klapuch\Storage;
use Tester;

abstract class MySqlDatabase extends Mockery {
	/** @var Storage\Database */
	protected $database;

	protected function setUp() {
		parent::setUp();
		Tester\Environment::lock('mysql_database', __DIR__ . '/../temporary');
		$this->database = $this->connection();
	}

	private function connection(): Storage\Database {
		$credentials = parse_ini_file(__DIR__ . '/.database.ini', true);
		$this->database = new Storage\PDODatabase(
			$credentials['MYSQL']['dsn'],
			$credentials['MYSQL']['user'],
			$credentials['MYSQL']['password']
		);
		return $this->database;
	}
}