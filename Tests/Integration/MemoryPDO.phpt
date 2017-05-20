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
			['name' => 'Dominik', 'title' => 'Developer']
		))->prepare('SELECT name, title FROM table');
		Assert::same(['name' => 'Dominik', 'title' => 'Developer'], $statement->fetch());
	}

	public function testFetchingRowFromMultipleArray() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Developer'],
			]
		))->prepare('SELECT name, title FROM table');
		Assert::same(['name' => 'Dominik', 'title' => 'Developer'], $statement->fetch());
	}

	public function testFetchingColumnFromArrayBySingleFieldQuery() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['name' => 'Dominik', 'title' => 'Developer']
		))->prepare('SELECT name FROM table');
		Assert::same('Dominik', $statement->fetchColumn());
	}

	public function testFetchingFirstColumnFromMultipleArray() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Programmer'],
			]
		))->prepare('SELECT title, name FROM table');
		Assert::same('Developer', $statement->fetchColumn());
	}

	public function testFetchingUnknownColumnFromArrayLeadingToFalse() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['name' => 'Dominik', 'title' => 'Developer']
		))->prepare('SELECT name, title FROM table');
		Assert::false($statement->fetchColumn(3));
	}

	public function testFetchingFirstColumnFromArrayByQuery() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			['foo' => 'bar', 'name' => 'Dominik']
		))->prepare('SELECT name, title FROM table');
		Assert::same('Dominik', $statement->fetchColumn());
	}

	public function testFetchingAllFromArray() {
		$statement = (new Storage\MemoryPDO(
			$this->database,
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Developer'],
			]
		))->prepare('SELECT');
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
			['name' => 'Dominik', 'title' => 'Developer']
		))->prepare("INSERT INTO test (name) VALUES ('foo')");
		$statement->execute();
		$p = $this->database->prepare('SELECT name FROM test');
		$p->execute();
		Assert::same('foo', $p->fetchColumn());
	}
}

(new MemoryPDO())->run();