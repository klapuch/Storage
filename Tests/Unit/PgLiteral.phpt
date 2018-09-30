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

final class PgLiteral extends Tester\TestCase {
	/**
	 * @dataProvider conversions
	 */
	public function testConversions($from, $to) {
		Assert::same($to, (new Storage\PgLiteral($from))->value());
	}

	protected function conversions(): array {
		return [
			[null, "'null'"],
			[true, "'true'"],
			[false, "'false'"],
			[20, "'20'"],
			['abc', "'abc'"],
		];
	}
}

(new PgLiteral())->run();