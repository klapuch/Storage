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

final class MemoryConnection extends TestCase\PostgresDatabase {
	public function testFetchingRowOrRowsFromArray() {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			['name' => 'Dominik', 'title' => 'Developer']
		))->prepare('SELECT name, title FROM table');
		Assert::same(['name' => 'Dominik', 'title' => 'Developer'], $statement->fetch());
		Assert::same(['name' => 'Dominik', 'title' => 'Developer'], $statement->fetchAll());
	}

	public function testFetchingRowOrRowsFromMultipleArray() {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Developer'],
			],
			[]
		))->prepare('SELECT name, title FROM table');
		Assert::same(
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Developer'],
			],
			$statement->fetch()
		);
		Assert::same(
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Developer'],
			],
			$statement->fetchAll()
		);
	}

	public function testFetchingFirstColumnFromArrayByQuery() {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			['name' => 'Dominik', 'title' => 'Developer']
		))->prepare('SELECT name, title FROM table');
		Assert::same('Dominik', $statement->fetchColumn());
	}

	public function testFetchingUnknownColumnFromArrayLeadingToFalse() {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			['name' => 'Dominik', 'title' => 'Developer']
		))->prepare('SELECT name, title FROM table');
		Assert::false($statement->fetchColumn(3));
	}

	public function testExecutingOriginQuery() {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			['name' => 'Dominik', 'title' => 'Developer']
		))->prepare("INSERT INTO test (name) VALUES ('foo')");
		$statement->execute();
		$p = $this->connection->prepare('SELECT name FROM test');
		$p->execute();
		Assert::same('foo', $p->fetchColumn());
	}

	public function testFetchingColumnAsNumericLiteralUsingOriginal() {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			['foo' => 'bar', 'name' => 'Dominik']
		))->prepare('SELECT 1');
		$statement->execute([]);
		Assert::same(1, $statement->fetchColumn());
	}

	public function testFetchingColumnAsStringLiteralUsingOriginal() {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			['foo' => 'bar', 'name' => 'Dominik']
		))->prepare("SELECT 'abc'");
		$statement->execute([]);
		Assert::same('abc', $statement->fetchColumn());
	}

	public function testExecutingFunctionOnOriginal() {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			['foo' => 'bar', 'name' => 'Dominik']
		))->prepare("SELECT hstore('a=>b'::hstore)");
		$statement->execute([]);
		Assert::same('"a"=>"b"', $statement->fetchColumn());
	}
}

(new MemoryConnection())->run();