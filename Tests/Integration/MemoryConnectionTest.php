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
final class MemoryConnectionTest extends TestCase\PostgresDatabase {
	public function testFetchingRowOrRowsFromArray(): void {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			['name' => 'Dominik', 'title' => 'Developer'],
		))->prepare('SELECT name, title FROM table');
		Assert::same(['name' => 'Dominik', 'title' => 'Developer'], $statement->fetch());
		Assert::same(['name' => 'Dominik', 'title' => 'Developer'], $statement->fetchAll());
	}

	public function testFetchingRowOrRowsFromMultipleArray(): void {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Developer'],
			],
		))->prepare('SELECT name, title FROM table');
		Assert::same(
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Developer'],
			],
			$statement->fetch(),
		);
		Assert::same(
			[
				['name' => 'Dominik', 'title' => 'Developer'],
				['name' => 'Jacob', 'title' => 'Developer'],
			],
			$statement->fetchAll(),
		);
	}

	public function testFetchingFirstColumnFromArrayByQuery(): void {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			['name' => 'Dominik', 'title' => 'Developer'],
		))->prepare('SELECT name, title FROM table');
		Assert::same('Dominik', $statement->fetchColumn());
	}

	public function testFetchingUnknownColumnFromArrayLeadingToFalse(): void {
		$statement = (new Storage\MemoryConnection(
			$this->connection,
			['name' => 'Dominik', 'title' => 'Developer'],
		))->prepare('SELECT name, title FROM table');
		Assert::false($statement->fetchColumn(3));
	}
}

(new MemoryConnectionTest())->run();
