<?php
declare(strict_types = 1);

namespace Klapuch\Storage\TestCase;

use Klapuch\Storage;
use Predis;
use Tester;

abstract class PostgresDatabase extends Mockery {
	/** @var \Klapuch\Storage\Connection */
	protected $connection;

	/** @var \PDO */
	protected $pdo;

	/** @var \Predis\Client */
	protected $redis;

	/** @var \SplFileInfo */
	private $file;

	/** @var Storage\Schema */
	protected $schema;

	protected function setUp() {
		parent::setUp();
		Tester\Environment::lock('postgres', __DIR__ . '/../Temporary');
		$credentials = (array) parse_ini_file(__DIR__ . '/../Configuration/.config.local.ini', true);
		$this->file = new \SplFileInfo(__DIR__ . '/../Temporary/db_schema.php');
		$this->connection = $this->connection($credentials);
		$this->schema = new Storage\CachedSchema($this->connection, $this->file);
		$this->schema->generate();
		$this->connection->exec('TRUNCATE test');
		$this->connection->exec('TRUNCATE test');
		$this->connection->exec('TRUNCATE test_table2');
		$this->connection->exec('TRUNCATE test_full');
		$this->connection->exec('TRUNCATE test_table4');
		$this->connection->exec('TRUNCATE person_table');
		$this->connection->exec('TRUNCATE person_table2');
		$this->connection->exec('TRUNCATE simple_table');
		$this->connection->exec('TRUNCATE coordinates_table');
		$this->connection->exec('TRUNCATE scalars');
		$this->connection->exec('TRUNCATE pg_types');
	}

	protected function tearDown(): void {
		parent::tearDown();
		@unlink($this->file->getPathname());
	}

	private function connection(array $credentials): Storage\CachedConnection {
		$this->pdo = new Storage\SafePDO(
			$credentials['POSTGRES']['dsn'],
			$credentials['POSTGRES']['user'],
			$credentials['POSTGRES']['password']
		);
		return new Storage\CachedConnection(
			new Storage\PDOConnection($this->pdo),
			$this->file
		);
	}
}