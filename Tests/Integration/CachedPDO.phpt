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
	public function testCachingForStatement() {
		$pdo = new Storage\CachedPDO($this->database);
		Assert::same($pdo->prepare('SELECT 1'), $pdo->prepare('SELECT 1'));
	}

	public function testNoCacheForDifferentStatements() {
		$pdo = new Storage\CachedPDO($this->database);
		Assert::notSame($pdo->prepare('SELECT 1'), $pdo->prepare('SELECT 2'));
	}

	public function testPersistentCache() {
		Assert::same(
			(new Storage\CachedPDO($this->database))->prepare('SELECT 1'),
			(new Storage\CachedPDO($this->database))->prepare('SELECT 1')
		);
	}
}

(new CachedPDO())->run();