<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1.0
 */
namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class Transaction extends TestCase\PostgresDatabase {
	/** @var \Klapuch\Storage\Transaction */
	private $transaction;

	public function setUp() {
		parent::setUp();
		$this->transaction = new Storage\Transaction($this->database);
	}

	public function testTransactionWithReturnedValue() {
		$result = $this->transaction->start(
			function() {
				$this->database->exec("INSERT INTO test (id, name) VALUES (1, 'foo')");
				$this->database->exec("INSERT INTO test (id, name) VALUES (2, 'bar')");
				$this->database->exec('DELETE FROM test WHERE id = 2');
				return 666;
			}
		);
		$statement = $this->database->prepare('SELECT id, name FROM test');
		$statement->execute();
		Assert::same(666, $result);
		Assert::equal([['id' => 1, 'name' => 'foo']], $statement->fetchAll());
	}

	public function testForcedExceptionWithRollback() {
		Assert::exception(
			function() {
				$this->transaction->start(
					function() {
						$this->database->exec("INSERT INTO test (name) VALUES ('foo')");
						$this->database->exec("INSERT INTO test (name) VALUES ('foo2')");
						throw new \DomainException('foo');
					}
				);
			},
			\DomainException::class,
			'foo'
		);
		$statement = $this->database->prepare('SELECT id, name FROM test');
		$statement->execute();
		Assert::equal([], $statement->fetchAll());
	}

	public function testPassingWithNestedTransaction() {
		Assert::noError(
			function() {
				(new Storage\Transaction($this->database))->start(
					function() {
						$this->database->exec("INSERT INTO test (name) VALUES ('foo')");
						(new Storage\Transaction($this->database))->start(
							function() {
								$this->database->exec("INSERT INTO test (name) VALUES ('foo2')");
							}
						);
					}
				);
			}
		);
	}

	/**
	 * @throws \DomainException Forced exception
	 */
	public function testThrowingOnBeginTransactionWithoutRollback() {
		$ex = new \DomainException('Forced exception');
		$database = $this->mock(\PDO::class);
		$database->shouldReceive('query')
			->once()
			->with('START TRANSACTION')
			->andThrowExceptions([$ex]);
		(new Storage\Transaction($database))->start(function() {
		});
	}
}

(new Transaction())->run();