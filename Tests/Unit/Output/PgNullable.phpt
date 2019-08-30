<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1
 */

namespace Klapuch\Storage\Unit\Output;

use Klapuch\Storage\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PgNullable extends Tester\TestCase {
	public function testDelegatingForNotNull() {
		Assert::same('foo', (new Output\PgNullable('bar', new Output\FakeConversion('foo')))->value());
	}

	public function testNullForNull() {
		Assert::null((new Output\PgNullable(null, new Output\FakeConversion('foo')))->value());
	}
}

(new PgNullable())->run();