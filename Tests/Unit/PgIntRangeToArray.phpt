<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Klapuch\Storage\Unit;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class PgIntRangeToArray extends TestCase\PostgresDatabase {
	public function testConvertingToArray() {
		Assert::same(
			[10, 20, '[', ')'],
			(new Storage\PgIntRangeToArray('[10,20)'))->value()
		);
	}
}

(new PgIntRangeToArray())->run();