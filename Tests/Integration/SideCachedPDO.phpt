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

final class SideCachedPDO extends TestCase\PostgresDatabase {
	public function testCachingForStatement() {
		$pdo = new Storage\SideCachedPDO($this->database);
		Assert::same($pdo->prepare('SELECT 1'), $pdo->prepare('SELECT 1'));
	}

	public function testNoCacheForDifferentStatements() {
		$pdo = new Storage\SideCachedPDO($this->database);
		Assert::notSame($pdo->prepare('SELECT 1'), $pdo->prepare('SELECT 2'));
	}

	public function testPersistentCache() {
		Assert::same(
			(new Storage\SideCachedPDO($this->database))->prepare('SELECT 1'),
			(new Storage\SideCachedPDO($this->database))->prepare('SELECT 1')
		);
	}
}

(new SideCachedPDO())->run();