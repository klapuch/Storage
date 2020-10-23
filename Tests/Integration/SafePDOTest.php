<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Integration;

use Klapuch\Storage\SafePDO;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class SafePDOTest extends TestCase\PostgresDatabase {
	public function testThrowingOnError(): void {
		$pdo = new SafePDO($this->credentials['dsn'], $this->credentials['user'], $this->credentials['password']);
		$statement = $pdo->prepare('FOO');
		Assert::exception(static function () use ($statement): void {
			$statement->execute();
		}, \PDOException::class);
	}

	public function testDisabledEmulatePrepares(): void {
		$pdo = new SafePDO($this->credentials['dsn'], $this->credentials['user'], $this->credentials['password']);
		$statement = $pdo->prepare('SELECT 1;SELECT 1');
		Assert::exception(static function () use ($statement): void {
			$statement->execute();
		}, \PDOException::class);
	}

	public function testAssocFetch(): void {
		$pdo = new SafePDO($this->credentials['dsn'], $this->credentials['user'], $this->credentials['password']);
		$statement = $pdo->prepare('INSERT INTO test (id, name) VALUES (?, ?)');
		$statement->execute([1, 'first']);
		$statement = $pdo->prepare('SELECT id, name FROM test WHERE id = ?');
		$statement->execute([1]);
		Assert::equal(['id' => 1, 'name' => 'first'], $statement->fetch());
	}
}

(new SafePDOTest())->run();
