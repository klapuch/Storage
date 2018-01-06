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

require __DIR__ . '/../bootstrap.php';

final class MetaPDO extends TestCase\PostgresDatabase {
	public function testCachingOnSecondRun() {
		$this->database->exec('DROP TABLE IF EXISTS test_table2 CASCADE');
		$this->database->exec('CREATE TABLE test_table2 (first integer)');
		$meta = [
			[
				'attribute_name' => 'first',
				'data_type' => 'integer',
				'ordinal_position' => 1,
				'native_type' => 'integer',
			],
		];
		Assert::same(
			$meta,
			(new Storage\MetaPDO($this->database, $this->redis))->meta('test_table2')
		);
		Assert::same($meta, unserialize($this->redis->get('postgres:type:meta:test_table2')));
		Assert::same(
			$meta,
			(new Storage\MetaPDO($this->mock(\PDO::class), $this->mock(Predis\Client::class)))->meta('test_table2')
		);
	}

	public function testUsingNamespaceForRedis() {
		$this->database->exec('DROP TABLE IF EXISTS test_table2 CASCADE');
		$this->database->exec('CREATE TABLE test_table2 (first integer)');
		Assert::noError(function() {
			$meta = [
				[
					'attribute_name' => 'first',
					'data_type' => 'integer',
					'ordinal_position' => 1,
					'native_type' => 'integer',
				],
			];
			$redis = $this->mock(Predis\Client::class);
			$redis->shouldReceive('exists')->once()->with('postgres:type:meta:test_table2')->andReturn(false);
			$redis->shouldReceive('get')->once()->with('postgres:type:meta:test_table2')->andReturn(serialize($meta));
			$redis->shouldReceive('set')->once()->with('postgres:type:meta:test_table2', serialize($meta));
			$redis->shouldReceive('persist')->once()->with('postgres:type:meta:test_table2');
			(new Storage\MetaPDO($this->database, $redis))->meta('test_table2');
		});
	}

	public function testMetaForTypeAndTable() {
		$this->database->exec('DROP TABLE IF EXISTS test_table2 CASCADE');
		$this->database->exec('DROP TYPE IF EXISTS test_type2 CASCADE');
		$this->database->exec('CREATE TABLE test_table2 (first integer)');
		$this->database->exec('CREATE TYPE test_type2 AS (second integer)');
		Assert::same(
			[
				[
					'attribute_name' => 'second',
					'data_type' => 'integer',
					'ordinal_position' => 1,
					'native_type' => 'integer',
				],
			],
			(new Storage\MetaPDO($this->database, $this->redis))->meta('test_type2')
		);
		Assert::same(
			[
				[
					'attribute_name' => 'first',
					'data_type' => 'integer',
					'ordinal_position' => 1,
					'native_type' => 'integer',
				],
			],
			(new Storage\MetaPDO($this->database, $this->redis))->meta('test_table2')
		);
	}

	public function testMappingOfAvailableTypes() {
		$this->database->exec('DROP TABLE IF EXISTS test_full CASCADE');
		$this->database->exec(
			'CREATE TABLE test_full (
				one integer,
				two smallint,
				three bigint,
				four BOOLEAN,
				five NUMERIC,
				six text,
				seven VARCHAR(10),
				eight character VARYING,
				nine char
			)'
		);
		Assert::same(
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
			(new Storage\MetaPDO($this->database, $this->redis))->meta('test_full')
		);
	}

	public function testConvertingTypeInType() {
		$this->database->exec('DROP TYPE IF EXISTS test_type1 CASCADE');
		$this->database->exec('DROP TYPE IF EXISTS test_type2 CASCADE');
		$this->database->exec('CREATE TYPE test_type1 AS (second integer)');
		$this->database->exec('CREATE TYPE test_type2 AS (first test_type1)');
		Assert::same(
			[
				[
					'attribute_name' => 'first',
					'data_type' => 'test_type1',
					'ordinal_position' => 1,
					'native_type' => 'test_type1',
				],
			],
			(new Storage\MetaPDO($this->database, $this->redis))->meta('test_type2')
		);
	}

	public function testConvertingTypeInTable() {
		$this->database->exec('DROP TYPE IF EXISTS test_type3 CASCADE');
		$this->database->exec('DROP TABLE IF EXISTS test_table4 CASCADE');
		$this->database->exec('CREATE TYPE test_type3 AS (second integer)');
		$this->database->exec('CREATE TABLE test_table4 (first test_type3)');
		Assert::same(
			[
				[
					'attribute_name' => 'first',
					'data_type' => 'test_type3',
					'ordinal_position' => 1,
					'native_type' => 'test_type3',
				],
			],
			(new Storage\MetaPDO($this->database, $this->redis))->meta('test_table4')
		);
	}

	public function testIgnoringCases() {
		$this->database->exec('DROP TABLE IF EXISTS test_table2 CASCADE');
		$this->database->exec('DROP TYPE IF EXISTS test_type2 CASCADE');
		$this->database->exec('CREATE TABLE test_table2 (first integer)');
		$this->database->exec('CREATE TYPE test_type2 AS (second integer)');
		Assert::notSame([], (new Storage\MetaPDO($this->database, $this->redis))->meta('TEST_TYPE2'));
		Assert::notSame([], (new Storage\MetaPDO($this->database, $this->redis))->meta('TEST_TABLE2'));
	}

	public function testCachingColumnMeta() {
		$this->database->exec('DROP TABLE IF EXISTS test_table2 CASCADE');
		$this->database->exec('CREATE TABLE test_table2 (first integer, second text)');
		$statement = (new Storage\MetaPDO($this->database, $this->redis))->prepare('SELECT * FROM test_table2');
		$statement->execute();
		Assert::same('first', $statement->getColumnMeta(0)['name']);
		Assert::same('second', $statement->getColumnMeta(1)['name']);
		$statement = (new Storage\MetaPDO($this->database, $this->redis))->prepare('SELECT * FROM test_table2');
		Assert::same('first', $statement->getColumnMeta(0)['name']);
		Assert::same('second', $statement->getColumnMeta(1)['name']);
	}
}

(new MetaPDO())->run();