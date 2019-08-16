<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1.0
 */

namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Predis;
use Tester\Assert;
use Tester\FileMock;

require __DIR__ . '/../bootstrap.php';

final class CachedSchema extends TestCase\PostgresDatabase {
	public function testColumnsByTable() {
		$meta = [
			[
				'attribute_name' => 'first',
				'data_type' => 'integer',
				'ordinal_position' => 1,
				'native_type' => 'integer',
			],
		];
		Assert::equal($meta, $this->schema->columns('test_table2'));
	}

	public function testMetaForTypeAndTable() {
		Assert::equal(
			[
				[
					'attribute_name' => 'second',
					'data_type' => 'integer',
					'ordinal_position' => 1,
					'native_type' => 'integer',
				],
			],
			$this->schema->columns('test_type2')
		);
		Assert::equal(
			[
				[
					'attribute_name' => 'first',
					'data_type' => 'integer',
					'ordinal_position' => 1,
					'native_type' => 'integer',
				],
			],
			$this->schema->columns('test_table2')
		);
	}

	public function testMappingOfAvailableTypes() {
		Assert::equal(
			[
				[
					'attribute_name' => 'one',
					'data_type' => 'integer',
					'ordinal_position' => 1,
					'native_type' => 'integer',
				],
				[
					'attribute_name' => 'two',
					'data_type' => 'smallint',
					'ordinal_position' => 2,
					'native_type' => 'integer',
				],
				[
					'attribute_name' => 'three',
					'data_type' => 'bigint',
					'ordinal_position' => 3,
					'native_type' => 'integer',
				],
				[
					'attribute_name' => 'four',
					'data_type' => 'boolean',
					'ordinal_position' => 4,
					'native_type' => 'boolean',
				],
				[
					'attribute_name' => 'five',
					'data_type' => 'numeric',
					'ordinal_position' => 5,
					'native_type' => 'double',
				],
				[
					'attribute_name' => 'six',
					'data_type' => 'text',
					'ordinal_position' => 6,
					'native_type' => 'string',
				],
				[
					'attribute_name' => 'seven',
					'data_type' => 'character varying',
					'ordinal_position' => 7,
					'native_type' => 'string',
				],
				[
					'attribute_name' => 'eight',
					'data_type' => 'character varying',
					'ordinal_position' => 8,
					'native_type' => 'string',
				],
				[
					'attribute_name' => 'nine',
					'data_type' => 'character',
					'ordinal_position' => 9,
					'native_type' => 'string',
				],
			],
			$this->schema->columns('test_full')
		);
	}

	public function testConvertingTypeInType() {
		Assert::equal(
			[
				[
					'attribute_name' => 'first',
					'data_type' => 'test_type1',
					'ordinal_position' => 1,
					'native_type' => 'test_type1',
				],
			],
			$this->schema->columns('test_type3')
		);
	}

	public function testConvertingTypeInTable() {
		Assert::equal(
			[
				[
					'attribute_name' => 'first',
					'data_type' => 'test_type4',
					'ordinal_position' => 1,
					'native_type' => 'test_type4',
				],
			],
			$this->schema->columns('test_table4')
		);
	}

	public function testIgnoringCases() {
		Assert::notSame([], $this->schema->columns('TEST_TYPE2'));
		Assert::notSame([], $this->schema->columns('TEST_TABLE2'));
	}
}

(new CachedSchema())->run();