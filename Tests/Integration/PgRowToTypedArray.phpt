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

final class PgRowToTypedArray extends TestCase\PostgresDatabase {
	public function testTypingToProperPhpTypes() {
		Assert::same(
			['name' => 'Dom', 'age' => 21, 'cool' => true],
			(new Storage\PgRowToTypedArray(
				$this->connection,
				'(Dom,21,t)',
				'person_type3',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testTypingCorrectlyNumberInText() {
		Assert::same(
			['name' => '123', 'race' => 'ok'],
			(new Storage\PgRowToTypedArray(
				$this->connection,
				'(123,ok)',
				'person_type',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testPassingWithNull() {
		Assert::same(
			['name' => null, 'race' => null],
			(new Storage\PgRowToTypedArray(
				$this->connection,
				'(,)',
				'person_type',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testKeepingAlreadyConvertedValue() {
		Assert::same(
			[
				'name' => 'Dom',
				'length' => ['value' => 10, 'unit' => 'mm'],
			],
			(new Storage\PgRowToTypedArray(
				$this->connection,
				'("(10,mm)",Dom)',
				'person_table2',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testDelegatingWithUnknownType() {
		Assert::same(
			'foo',
			(new Storage\PgRowToTypedArray(
				$this->connection,
				'(bar)',
				'unknown_type',
				new Storage\FakeConversion('foo')
			))->value()
		);
	}
}

(new PgRowToTypedArray())->run();