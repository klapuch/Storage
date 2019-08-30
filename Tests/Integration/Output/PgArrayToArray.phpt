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

final class PgArrayToArray extends TestCase\PostgresDatabase {
	public function testConvertingToArray() {
		Assert::same(
			[1, 2, 3],
			(new Output\PgArrayToArray(
				$this->connection,
				'{1,2,3}',
				'INTEGER[]',
				new Output\FakeConversion()
			))->value()
		);
	}

	public function testConvertingViaNativeType() {
		Assert::same(
			[1, 2, 3],
			(new Output\PgArrayToArray(
				$this->connection,
				'{1,2,3}',
				'_int',
				new Output\FakeConversion()
			))->value()
		);
		Assert::same(
			['1', '2', '3'],
			(new Output\PgArrayToArray(
				$this->connection,
				'{1,2,3}',
				'_text',
				new Output\FakeConversion()
			))->value()
		);
	}

	public function testDelegatingNotArray() {
		Assert::same(
			'foo',
			(new Output\PgArrayToArray(
				$this->connection,
				'{1,2,3}',
				'text',
				new Output\FakeConversion('foo')
			))->value()
		);
	}
}

(new PgArrayToArray())->run();