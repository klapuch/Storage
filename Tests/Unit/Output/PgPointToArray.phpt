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

final class PgPointToArray extends Tester\TestCase {
	public function testConvertingToXAndY() {
		Assert::same(
			['x' => 50.556785, 'y' => 70.0],
			(new Output\PgPointToArray(
				'(50.556785,70)',
				'pOINt',
				new Output\FakeConversion()
			))->value()
		);
	}

	public function testDelegatingNotPoint() {
		Assert::same(
			'foo',
			(new Output\PgPointToArray(
				'xxx',
				'(50.556785,70)',
				new Output\FakeConversion('foo')
			))->value()
		);
	}
}

(new PgPointToArray())->run();