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

final class MemoryPDO extends TestCase\PostgresDatabase {
	public function testFetchingRowFromArray() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['name' => 'Dominik', 'title' => 'Developer'],
			[]
		))->prepare('SELECT name, title FROM table');
		Assert::same(['name' => 'Dominik', 'title' => 'Developer'], $statement->fetch());
	}

	public function testCaseInseitiveQuery() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['name' => 'Dominik', 'title' => 'Developer'],
			[]
		))->prepare('select name, title from table');
		Assert::same(['name' => 'Dominik', 'title' => 'Developer'], $statement->fetch());
	}

	public function testFetchingRowFromMultipleArray() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Developer'],
			],
			[]
		))->prepare('SELECT name, title FROM table');
		Assert::same(['name' => 'Dominik', 'title' => 'Developer'], $statement->fetch());
	}

	public function testFetchingColumnFromArrayBySingleFieldQuery() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['name' => 'Dominik', 'title' => 'Developer'],
			[]
		))->prepare('SELECT name FROM table');
		Assert::same('Dominik', $statement->fetchColumn());
	}

	public function testFetchingFirstColumnFromMultipleArray() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Programmer'],
			],
			[]
		))->prepare('SELECT title, name FROM table');
		Assert::same('Developer', $statement->fetchColumn());
	}

	public function testFetchingUnknownColumnFromArrayLeadingToFalse() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['name' => 'Dominik', 'title' => 'Developer'],
			[]
		))->prepare('SELECT name, title FROM table');
		Assert::false($statement->fetchColumn(3));
	}

	public function testFetchingFirstColumnFromArrayByQuery() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['foo' => 'bar', 'name' => 'Dominik'],
			[]
		))->prepare('SELECT name, title FROM table');
		Assert::same('Dominik', $statement->fetchColumn());
	}

	public function testFetchingAllFromArray() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Developer'],
			],
			[]
		))->prepare('SELECT * FROM xx');
		Assert::same(
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Developer'],
			],
			$statement->fetchAll()
		);
	}

	public function testExecutingOriginQuery() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['name' => 'Dominik', 'title' => 'Developer'],
			[]
		))->prepare("INSERT INTO test (name) VALUES ('foo')");
		$statement->execute();
		$p = $this->database->prepare('SELECT name FROM test');
		$p->execute();
		Assert::same('foo', $p->fetchColumn());
	}

	public function testFetchingColumnAsNumericLiteralUsingOriginal() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['foo' => 'bar', 'name' => 'Dominik'],
			[]
		))->prepare('SELECT 1');
		$statement->execute([]);
		Assert::same(1, $statement->fetchColumn());
	}

	public function testFetchingColumnAsStringLiteralUsingOriginal() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['foo' => 'bar', 'name' => 'Dominik'],
			[]
		))->prepare("SELECT 'abc'");
		$statement->execute([]);
		Assert::same('abc', $statement->fetchColumn());
	}

	public function testExecutingFunctionOnOriginal() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['foo' => 'bar', 'name' => 'Dominik'],
			[]
		))->prepare("SELECT hstore('a=>b'::hstore)");
		$statement->execute([]);
		Assert::same('"a"=>"b"', $statement->fetchColumn());
	}

	public function testSelectingForSpecifiedTablesInFromClause() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['foo' => 'bar', 'name' => 'Dominik'],
			['test']
		))->prepare('SELECT title FROM test');
		Assert::same(['foo' => 'bar', 'name' => 'Dominik'], $statement->fetchAll());
	}

	public function testMatchingWithAllFromAndJoinTables() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['foo' => 'bar', 'name' => 'Dominik'],
			['test2', 'test']
		))->prepare('SELECT title FROM test INNER JOIN test2 USING(id)');
		Assert::same(['foo' => 'bar', 'name' => 'Dominik'], $statement->fetchAll());
	}

	public function testMatchingWithMultipleJoins() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['foo' => 'bar', 'name' => 'Dominik'],
			['test2', 'test', 'test3']
		))->prepare('SELECT title FROM test INNER JOIN test2 USING(id) LEFT join test3 USING(ip)');
		Assert::same(['foo' => 'bar', 'name' => 'Dominik'], $statement->fetchAll());
	}

	public function testMatchingForAllSpecifiedTables() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['foo' => 'bar', 'name' => 'Dominik'],
			['test', 'test2']
		))->prepare('SELECT title FROM test');
		Assert::same([], $statement->fetchAll());
	}

	public function testCaseInsensitiveTables() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['foo' => 'bar', 'name' => 'Dominik'],
			['test']
		))->prepare('SELECT title from TEST');
		Assert::same(['foo' => 'bar', 'name' => 'Dominik'], $statement->fetchAll());
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['foo' => 'bar', 'name' => 'Dominik'],
			['TEST']
		))->prepare('SELECT title FROM test');
		Assert::same(['foo' => 'bar', 'name' => 'Dominik'], $statement->fetchAll());
	}
}

(new MemoryPDO())->run();