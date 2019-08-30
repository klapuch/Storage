<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1
 */

namespace Klapuch\Storage\Integration\Output;

use Klapuch\Storage\Output;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PgTimestamptzRangeToArray extends TestCase\PostgresDatabase {
	public function testConvertingToArray() {
		$ranges = (new Output\PgTimestamptzRangeToArray(
			$this->connection,
			'[2004-10-19 10:23:54.20+02,2005-10-19 10:23:54.20+02)',
			'TSTZrange',
			new Output\FakeConversion()
		))->value();
		[$from, $to, $left, $right] = $ranges;
		Assert::same('2004-10-19 08:23:54.200000+0000', (string) $from);
		Assert::same('2005-10-19 08:23:54.200000+0000', (string) $to);
		Assert::same('[', $left);
		Assert::same(')', $right);
	}

	public function testDelegatingNotTsTzRange() {
		Assert::same(
			'bar',
			(new Output\PgTimestamptzRangeToArray(
				$this->connection,
				'foo',
				'foo',
				new Output\FakeConversion('bar')
			))->value()
		);
	}
}

(new PgTimestamptzRangeToArray())->run();