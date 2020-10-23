<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Unit\Output;

use Klapuch\Storage\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class PgNullableTest extends Tester\TestCase {
	public function testDelegatingForNotNull(): void {
		Assert::same('foo', (new Output\PgNullable('bar', new Output\FakeConversion('foo')))->value());
	}

	public function testNullForNull(): void {
		Assert::null((new Output\PgNullable(null, new Output\FakeConversion('foo')))->value());
	}
}

(new PgNullableTest())->run();
