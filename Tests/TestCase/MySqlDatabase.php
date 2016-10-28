<?php
declare(strict_types = 1);
namespace Klapuch\Database\TestCase;

use Klapuch\Database;
use Tester;

abstract class MySqlDatabase extends Tester\TestCase {
	/** @var Database\Database */
	protected $database;

	protected function setUp() {
		parent::setUp();
		Tester\Environment::lock('mysql_database', __DIR__ . '/../temporary');
		$this->database = $this->connection();
	}

	private function connection(): Database\Database {
		$credentials = parse_ini_file(__DIR__ . '/.database.ini', true);
		$this->database = new Database\PDODatabase(
			$credentials['MYSQL']['dsn'],
			$credentials['MYSQL']['user'],
			$credentials['MYSQL']['password']
		);
		return $this->database;
	}
}