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
			(new Storage\PgArrayToArray(
				$this->database,
				'{1,2,3}',
				'INTEGER[]',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testConvertingViaNativeType() {
		Assert::same(
			[1, 2, 3],
			(new Storage\PgArrayToArray(
				$this->database,
				'{1,2,3}',
				'_int',
				new Storage\FakeConversion()
			))->value()
		);
		Assert::same(
			['1', '2', '3'],
			(new Storage\PgArrayToArray(
				$this->database,
				'{1,2,3}',
				'_text',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testDelegatingNotArray() {
		Assert::same(
			'foo',
			(new Storage\PgArrayToArray(
				$this->database,
				'{1,2,3}',
				'text',
				new Storage\FakeConversion('foo')
			))->value()
		);
	}
}

(new PgArrayToArray())->run();