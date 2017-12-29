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

final class CachedPDO extends TestCase\PostgresDatabase {
	public function testCachingSecondQuery() {
		$this->database->exec("INSERT INTO test(id, name, type, flag) VALUES (1, 'Dom', 'A', true)");
		$statement = (new Storage\CachedPDO($this->database))->prepare('SELECT name FROM test WHERE id = ?');
		$statement->execute([1]);
		Assert::same('Dom', $statement->fetchColumn());
		$statement = (new Storage\CachedPDO($this->mock(\PDO::class)))->prepare('SELECT name FROM test WHERE id = ?');
		$statement->execute([1]);
		Assert::same('Dom', $statement->fetchColumn());
	}

	public function testSeparateCachingForFetches() {
		$this->database->exec("INSERT INTO test(id, name, type, flag) VALUES (1, 'Dom', 'A', true)");
		$statement = (new Storage\CachedPDO($this->database))->prepare('SELECT name FROM test WHERE id = ?');
		$statement->execute([1]);
		Assert::same([['name' => 'Dom']], $statement->fetchAll());
		$statement = (new Storage\CachedPDO($this->database))->prepare('SELECT name FROM test WHERE id = ?');
		$statement->execute([1]);
		Assert::same(['name' => 'Dom'], $statement->fetch());
		$statement = (new Storage\CachedPDO($this->database))->prepare('SELECT name FROM test WHERE id = ?');
		$statement->execute([1]);
		Assert::same('Dom', $statement->fetchColumn());
	}

	public function testCachingByPassedParameters() {
		$this->database->exec("INSERT INTO test(id, name, type, flag) VALUES (1, 'Dom', 'A', true)");
		$this->database->exec("INSERT INTO test(id, name, type, flag) VALUES (2, 'You', 'B', false)");
		$statement = (new Storage\CachedPDO($this->database))->prepare('SELECT name FROM test WHERE id = ?');
		$statement->execute([1]);
		Assert::same([['name' => 'Dom']], $statement->fetchAll());
		$statement = (new Storage\CachedPDO($this->database))->prepare('SELECT name FROM test WHERE id = ?');
		$statement->execute([2]);
		Assert::same([['name' => 'You']], $statement->fetchAll());
		$statement = (new Storage\CachedPDO($this->mock(\PDO::class)))->prepare('SELECT name FROM test WHERE id = ?');
		$statement->execute([1]);
		Assert::same([['name' => 'Dom']], $statement->fetchAll());
		$statement = (new Storage\CachedPDO($this->mock(\PDO::class)))->prepare('SELECT name FROM test WHERE id = ?');
		$statement->execute([2]);
		Assert::same([['name' => 'You']], $statement->fetchAll());
	}

	public function testCachingNull() {
		$this->database->exec("INSERT INTO test(id, name, type, flag) VALUES (1, 'Dom', null, true)");
		$statement = (new Storage\CachedPDO($this->database))->prepare('SELECT type FROM test WHERE id = ?');
		$statement->execute([1]);
		Assert::null($statement->fetchColumn());
		$statement = (new Storage\CachedPDO($this->mock(\PDO::class)))->prepare('SELECT type FROM test WHERE id = ?');
		$statement->execute([1]);
		Assert::null($statement->fetchColumn());
	}
}

(new CachedPDO())->run();