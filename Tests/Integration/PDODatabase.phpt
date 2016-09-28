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

final class PDODatabase extends TestCase\PostgresDatabase {
	public function setUp() {
		parent::setUp();
		$this->database->query('TRUNCATE test');
	}

	/**
	 * @throws \RuntimeException Connection to database was not successful
	 */
	public function testWrongCredentials() {
		new Storage\PDODatabase(
			'???????',
			'!!!!!!!',
			',,,,,,,'
		);
	}

	public function testFetchingWithDisabledEmulatePrepares() {
		$this->database->query(
			'INSERT INTO test (id, name) VALUES (?, ?)',
			[5, 'foo']
		);
		Assert::equal(
			['id' => 5, 'name' => 'foo'],
			$this->database->fetch(
				'SELECT id, name FROM test WHERE id = ? LIMIT ? OFFSET ?',
				[5, 10, 0]
			)
		);
	}

	public function testFormatOfFetchingAll() {
		$this->database->query(
			'INSERT INTO test (id, name) VALUES (?, ?), (?, ?)',
			[5, 'foo', 6, 'bar']
		);
		$rows = $this->database->fetchAll('SELECT id, name FROM test');
		Assert::same(2, count($rows));
		Assert::same(5, $rows[0]['id']);
		Assert::same('foo', $rows[0]['name']);
		Assert::same(6, $rows[1]['id']);
		Assert::same('bar', $rows[1]['name']);
	}

	public function testFormatOfFetching() {
		$this->database->query(
			'INSERT INTO test (id, name) VALUES (?, ?), (?, ?)',
			[5, 'foo', 6, 'bar']
		);
		$rows = $this->database->fetch('SELECT id, name FROM test');
		Assert::same(2, count($rows));
		Assert::same(5, $rows['id']);
		Assert::same('foo', $rows['name']);
    }

    public function testFetchingUnknownValue() {
        Assert::same(
            [],
            $this->database->fetch('SELECT id, name FROM test')
        );
    }

    public function testFetchingAllUnknownValue() {
        Assert::same(
            [],
            $this->database->fetchAll('SELECT id, name FROM test')
        );
	}

	public function testFetchingWithNamedPlaceholders() {
		$this->database->query(
			'INSERT INTO test (id, name) VALUES (:id, :fooName)',
			[':id' => 5, ':fooName' => 'foo']
		);
		$rows = $this->database->fetch('SELECT id, name FROM test');
		Assert::same(2, count($rows));
		Assert::same(5, $rows['id']);
		Assert::same('foo', $rows['name']);
	}

	/**
	 * @throws \PDOException Parameters must be either named or placeholders
	 */
	public function testCombinationNamedAndPlaceholders() {
		$this->database->query(
			'INSERT INTO test (id, name) VALUES (:id, :fooName)',
			[':id' => 5, 1 => 'foo']
		);
	}

	public function testFormatOfFetchingColumn() {
		$this->database->query(
			'INSERT INTO test (id, name) VALUES (?, ?)',
			[5, 'foo']
		);
		$name = $this->database->fetchColumn(
			'SELECT name FROM test WHERE id = ?',
			[5]
		);
		Assert::same('foo', $name);
	}

	/**
	 * @throws \PDOException Parameters must be either named or placeholders
	 */
	public function testQueryWithStringAssociativeKeys() {
		$this->database->query(
			'INSERT INTO test (id, name) VALUES (?, ?)',
			['ONE' => 5, 'two' => 'foo']
		);
		Assert::true(true);
	}

	public function testRaisingIntegrityConstraintOnUnique() {
		$ex = Assert::exception(function() {
			$this->database->query(
				'INSERT INTO test (id, name, type) VALUES
				(?, ?, ?), (?, ?, ?)',
				[1, 'A', 'X', 2, 'B', 'X']
			);
		}, Storage\UniqueConstraint::class);
		Assert::type(\PDOException::class, $ex->getPrevious());
		Assert::same(23505, $ex->getCode());
	}

	/**
	 * @throws \PDOException
	 */
	public function testRethrowingExceptionOnRegularError() {
		$this->database->query(
			'INSERT INTO test (id, name, type) VALUES
			(?, ?, ?)',
			[1, 'A', 22]
		);
	}
}

(new PDODatabase())->run();
