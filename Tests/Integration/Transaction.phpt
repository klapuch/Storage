<?php

/**
* @testCase
* @phpVersion > 7.0.0
*/

namespace Bulletpoint\Model\Integration;

use Tester;
use Tester\Assert;
use Klapuch\{
	Storage, TestCase
};

require __DIR__ . '/../bootstrap.php';

final class Transaction extends TestCase\Database {
	/** @var Storage\Transaction */
	private $transaction;

	public function setUp() {
		parent::setUp();
		$this->transaction = new Storage\Transaction($this->database);
		$this->database->query('TRUNCATE test');
	}

	public function testSuccessfulTransactionWithReturnedValue() {
		$lastId = $this->transaction->start(function() {
			$this->database->query('INSERT INTO test (name) VALUES ("foo")');
			$this->database->query('INSERT INTO test (name) VALUES ("foo2")');
			$foo2Id = $this->database->fetchColumn('SELECT LAST_INSERT_ID()');
			$this->database->query('DELETE FROM test WHERE name = "foo2"');
			return $foo2Id;
		});
		Assert::same(2, $lastId);
		Assert::equal(
			[['ID' => 1, 'name' => 'foo']],
			$this->database->fetchAll('SELECT * FROM test')
		);
	}

	public function testForcedPdoExceptionWithRollback() {
		$exception = Assert::exception(function() {
			$this->transaction->start(function() {
				$this->database->query('INSERT INTO test (name) VALUES ("foo")');
				$this->database->query('INSERT INTO test (name) VALUES ("foo2")');
				$this->database->query('SOMETHING STRANGE TO DATABASE!');
			});
		}, \RuntimeException::class, 'Error on the database side. Rolling back.');
		Assert::type('\PDOException', $exception->getPrevious());
		Assert::equal(
			[],
			$this->database->fetchAll('SELECT * FROM test')
		);
	}

	public function testForcedGeneralExceptionWithRollback() {
		Assert::exception(function() {
			$this->transaction->start(function() {
				$this->database->query('INSERT INTO test (name) VALUES ("foo")');
				$this->database->query('INSERT INTO test (name) VALUES ("foo2")');
				throw new \RuntimeException('Forced exception');
			});
		}, '\RuntimeException', 'Forced exception');
		Assert::equal(
			[],
			$this->database->fetchAll('SELECT * FROM test')
		);
	}
}

(new Transaction())->run();