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

	public function testConvertingToViaNativeType() {
		Assert::same(
			[1, 2, 3],
			(new Storage\PgArrayToArray($this->database, '{1,2,3}', 'int4'))->value()
		);
		Assert::same(
			[1, 2, 3],
			(new Storage\PgArrayToArray($this->database, '{1,2,3}', 'int2'))->value()
		);
	}
}

(new PgArrayToArray())->run();