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

final class PDODatabase extends TestCase\Database {
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
			',,,,,,,',
			'¨¨¨¨¨¨¨¨'
		);
	}

	public function testFetchingWithDisabledEmulatePrepares() {
		$this->database->query(
			'INSERT INTO test (ID, name) VALUES (?, ?)',
			[5, 'foo']
		);
		Assert::equal(
			['ID' => 5, 'name' => 'foo'],
			$this->database->fetch(
				'SELECT * FROM test WHERE ID = ? LIMIT ?, ?',
				[5, 0, 10]
			)
		);
	}

	public function testFormatOfFetchingAll() {
		$this->database->query(
			'INSERT INTO test (ID, name) VALUES (?, ?), (?, ?)',
			[5, 'foo', 6, 'bar']
		);
		$rows = $this->database->fetchAll('SELECT * FROM test');
		Assert::same(2, count($rows));
		Assert::same(5, $rows[0]['ID']);
		Assert::same('foo', $rows[0]['name']);
		Assert::same(6, $rows[1]['ID']);
		Assert::same('bar', $rows[1]['name']);
	}

	public function testFormatOfFetching() {
		$this->database->query(
			'INSERT INTO test (ID, name) VALUES (?, ?), (?, ?)',
			[5, 'foo']
		);
		$rows = $this->database->fetch('SELECT * FROM test');
		Assert::same(1, count($rows));
		Assert::same(5, $rows['ID']);
		Assert::same('foo', $rows['name']);
	}

	public function testFormatOfFetchingColumn() {
		$this->database->query(
			'INSERT INTO test (ID, name) VALUES (?, ?)',
			[5, 'foo']
		);
		$name = $this->database->fetchColumn(
			'SELECT name FROM test WHERE ID = ?',
			[5]
		);
		Assert::same('foo', $name);
	}

	public function testQueryWithStringAssociativeKeys() {
		$this->database->query(
			'INSERT INTO test (ID, name) VALUES (?, ?)',
			['ONE' => 5, 'two' => 'foo']
		);
		Assert::true(true);
	}
}

(new PDODatabase())->run();