<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class TransactionTest extends TestCase\PostgresDatabase {
	public function testTransactionWithReturnedValue(): void {
		$result = (new Storage\Transaction($this->connection))->start(
			function(): int {
				$this->connection->exec("INSERT INTO test (id, name) VALUES (1, 'foo')");
				$this->connection->exec("INSERT INTO test (id, name) VALUES (2, 'bar')");
				$this->connection->exec('DELETE FROM test WHERE id = 2');
				return 666;
			},
		);
		$statement = $this->connection->prepare('SELECT id, name FROM test');
		$statement->execute();
		Assert::same(666, $result);
		Assert::equal([['id' => 1, 'name' => 'foo']], $statement->fetchAll());
	}

	public function testForcedExceptionWithRollback(): void {
		Assert::exception(
			function(): void {
				(new Storage\Transaction($this->connection))->start(
					function(): void {
						$this->connection->exec("INSERT INTO test (name) VALUES ('foo')");
						$this->connection->exec("INSERT INTO test (name) VALUES ('foo2')");
						throw new \DomainException('foo');
					},
				);
			},
			\DomainException::class,
			'foo',
		);
		$statement = $this->connection->prepare('SELECT id, name FROM test');
		$statement->execute();
		Assert::equal([], $statement->fetchAll());
	}

	public function testPassingWithNestedTransaction(): void {
		Assert::noError(
			function(): void {
				(new Storage\Transaction($this->connection))->start(
					function(): void {
						$this->connection->exec("INSERT INTO test (name) VALUES ('foo')");
						(new Storage\Transaction($this->connection))->start(
							function(): void {
								$this->connection->exec("INSERT INTO test (name) VALUES ('foo2')");
							},
						);
					},
				);
			},
		);
	}

	/**
	 * @throws \DomainException Forced exception
	 */
	public function testThrowingOnBeginTransactionWithoutRollback(): void {
		$ex = new \DomainException('Forced exception');
		$database = $this->mock(Storage\Connection::class);
		$database->shouldReceive('exec')
			->once()
			->andThrowExceptions([$ex])
			->getMock();
		assert($database instanceof Storage\Connection);
		(new Storage\Transaction($database))->start(static function(): void {
		});
	}
}

(new TransactionTest())->run();
