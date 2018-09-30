<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1.0
 */

namespace Klapuch\Storage\Integration;

use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class SafePDO extends TestCase\PostgresDatabase {
	/**
	 * @throws \PDOException
	 */
	public function testThrowingOnError() {
		$statement = $this->pdo->prepare('FOO');
		$statement->execute();
	}

	/**
	 * @throws \PDOException
	 */
	public function testDisabledEmulatePrepares() {
		$statement = $this->pdo->prepare('SELECT 1;SELECT 1');
		$statement->execute();
	}

	public function testAssocFetch() {
		$statement = $this->pdo->prepare('INSERT INTO test (id, name) VALUES (?, ?)');
		$statement->execute([1, 'first']);
		$statement = $this->pdo->prepare('SELECT id, name FROM test WHERE id = ?');
		$statement->execute([1]);
		Assert::equal(
			['id' => 1, 'name' => 'first'],
			$statement->fetch()
		);
	}
}

(new SafePDO())->run();