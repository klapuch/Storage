<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Klapuch\Storage\Integration;

use Tester\Assert;
use Klapuch\Storage;
use Klapuch\Storage\TestCase;

require __DIR__ . '/../bootstrap.php';

final class PostgresTransaction extends TestCase\PostgresDatabase {
	/** @var Storage\Transaction */
	private $transaction;

	public function setUp() {
		parent::setUp();
		$this->transaction = new Storage\PostgresTransaction($this->database);
		$this->database->query('TRUNCATE test');
	}

	public function testSuccessfulTransactionWithReturnedValue() {
		$lastName = $this->transaction->start(
			function() {
				$this->database->query(
					"INSERT INTO test (id, name) VALUES (1, 'foo')"
				);
				$this->database->query(
					"INSERT INTO test (id, name) VALUES (2, 'foo2')"
				);
				$foo2 = $this->database->fetchColumn(
					"SELECT name FROM test WHERE id = 2"
				);
				$this->database->query("DELETE FROM test WHERE name = 'foo2'");
				return $foo2;
			}
		);
		Assert::same('foo2', $lastName);
		Assert::equal(
			[['id' => 1, 'name' => 'foo']],
			$this->database->fetchAll('SELECT * FROM test')
		);
	}

	public function testForcedPdoExceptionWithRollback() {
		$exception = Assert::exception(
			function() {
				$this->transaction->start(
					function() {
						$this->database->query(
							"INSERT INTO test (name) VALUES ('foo')"
						);
						$this->database->query(
							"INSERT INTO test (name) VALUES ('foo2')"
						);
						$this->database->query(
							'SOMETHING STRANGE TO DATABASE!'
						);
					}
				);
			},
			'\RuntimeException',
			'Error on the database side. Rolled back.'
		);
		Assert::type('\PDOException', $exception->getPrevious());
		Assert::equal(
			[],
			$this->database->fetchAll('SELECT * FROM test')
		);
	}

	public function testForcedGeneralExceptionWithRollback() {
		Assert::exception(
			function() {
				$this->transaction->start(
					function() {
						$this->database->query(
							"INSERT INTO test (name) VALUES ('foo')"
						);
						$this->database->query(
							"INSERT INTO test (name) VALUES ('foo2')"
						);
						throw new \RuntimeException('Forced exception');
					}
				);
			},
			'\RuntimeException',
			'Forced exception'
		);
		Assert::equal(
			[],
			$this->database->fetchAll('SELECT * FROM test')
		);
	}

	public function testNestedTransaction() {
		Assert::exception(
			function() {
				$this->transaction->start(
					function() {
						$this->database->query(
							"INSERT INTO test (name) VALUES ('foo')"
						);
						$this->transaction->start(function() {
							$this->database->query(
								"INSERT INTO test (name) VALUES ('foo2')"
							);
							throw new \RuntimeException('Forced exception');
						});
					}
				);
			},
			'\RuntimeException',
			'Forced exception'
		);
		Assert::equal(
			[],
			$this->database->fetchAll('SELECT * FROM test')
		);
	}
}

(new PostgresTransaction())->run();