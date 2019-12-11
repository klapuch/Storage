<?php
declare(strict_types = 1);

namespace Klapuch\Storage\TestCase;

use Klapuch\Storage;
use Tester;

abstract class PostgresDatabase extends Mockery {
	/** @var \Klapuch\Storage\Connection */
	protected $connection;

	/** @var \PDO */
	protected $pdo;

	protected function setUp() {
		parent::setUp();
		Tester\Environment::lock('postgres', __DIR__ . '/../Temporary');
		$credentials = (array) parse_ini_file(__DIR__ . '/../Configuration/.config.local.ini', true);
		$this->connection = $this->connection($credentials);
		$this->connection->exec('TRUNCATE test');
	}

	private function connection(array $credentials): Storage\Connection {
		$this->pdo = new Storage\SafePDO(
			$credentials['POSTGRES']['dsn'],
			$credentials['POSTGRES']['user'],
			$credentials['POSTGRES']['password']
		);
		return new Storage\CachedConnection(
			new Storage\PDOConnection($this->pdo),
			new \SplFileInfo(Tester\FileMock::create())
		);
	}
}