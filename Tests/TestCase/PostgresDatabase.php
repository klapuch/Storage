<?php
declare(strict_types = 1);

namespace Klapuch\Storage\TestCase;

use Klapuch\Storage;
use Tester;

abstract class PostgresDatabase extends Mockery {
	protected Storage\Connection $connection;

	/** @var array<string, string> */
	protected array $credentials;

	protected function setUp(): void {
		parent::setUp();
		Tester\Environment::lock('postgres', __DIR__ . '/../Temporary');
		$credentials = (array) parse_ini_file(__DIR__ . '/../Configuration/.config.local.ini', true);
		$this->credentials = $credentials['POSTGRES'];
		$this->connection = new Storage\PDOConnection(
			new Storage\SafePDO(
				$this->credentials['dsn'],
				$this->credentials['user'],
				$this->credentials['password'],
			),
		);
		$this->connection->exec('TRUNCATE test');
	}
}
