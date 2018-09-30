<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1
 */

namespace Klapuch\Storage\Unit;

use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class PgIntRangeToArray extends Tester\TestCase {
	public function testConvertingToArray() {
		Assert::same(
			[10, 20, '[', ')'],
			(new Storage\PgIntRangeToArray(
				'[10,20)',
				'INT4range',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testDelegatingNotInt4Range() {
		Assert::same(
			'foo',
			(new Storage\PgIntRangeToArray(
				'[10,20)',
				'bar',
				new Storage\FakeConversion('foo')
			))->value()
		);
	}
}

(new PgIntRangeToArray())->run();