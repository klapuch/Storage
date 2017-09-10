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

final class PostgresArray extends TestCase\PostgresDatabase {
	public function testCastingToArray() {
		Assert::same(
			[1, 2, 3],
			(new Storage\PostgresArray($this->database, '{1,2,3}', 'INTEGER'))->cast()
		);
	}
}

(new PostgresArray())->run();