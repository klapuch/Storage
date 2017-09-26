<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class PgArrayToArray extends TestCase\PostgresDatabase {
	public function testConvertingToArray() {
		Assert::same(
			[1, 2, 3],
			(new Storage\PgArrayToArray($this->database, '{1,2,3}', 'INTEGER'))->value()
		);
	}

	public function testAllowingNull() {
		Assert::null((new Storage\PgArrayToArray($this->database, null, 'INTEGER'))->value());
	}
}

(new PgArrayToArray())->run();