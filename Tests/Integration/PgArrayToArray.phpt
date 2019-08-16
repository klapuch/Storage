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
				$this->connection,
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
				$this->connection,
				'{1,2,3}',
				'_int',
				new Storage\FakeConversion()
			))->value()
		);
		Assert::same(
			['1', '2', '3'],
			(new Storage\PgArrayToArray(
				$this->connection,
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
				$this->connection,
				'{1,2,3}',
				'text',
				new Storage\FakeConversion('foo')
			))->value()
		);
	}

	public function testConversionOfArrayOfTableTypes() {
		Assert::same(
			[
				['name' => 'first', 'id' => 1],
				['name' => 'second', 'id' => 2],
			],
			(new Storage\PgArrayToArray(
				$this->connection,
				'{"(1,first)","(2,second)"}',
				'_simple_table',
				new Storage\FakeConversion('simple_table')
			))->value()
		);
	}

	public function testConversionOfArrayOfTableTypesWithComplexColumn() {
		Assert::same(
			[
				['coordinates' => ['x' => 50.5, 'y' => 60.5], 'id' => 1],
				['coordinates' => ['x' => 50.6, 'y' => 60.6], 'id' => 2],
			],
			(new Storage\PgArrayToArray(
				$this->connection,
				'{"(1,\"(50.5,60.5)\")","(2,\"(50.6,60.6)\")"}',
				'_coordinates_table',
				new Storage\FakeConversion('coordinates_table')
			))->value()
		);
	}
}

(new PgArrayToArray())->run();