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

final class PgNullable extends Tester\TestCase {
	public function testDelegatingForNotNull() {
		Assert::same('foo', (new Storage\PgNullable('bar', new Storage\FakeConversion('foo')))->value());
	}

	public function testNullForNull() {
		Assert::null((new Storage\PgNullable(null, new Storage\FakeConversion('foo')))->value());
	}
}

(new PgNullable())->run();