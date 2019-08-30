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

	public function testConversionOfArrayOfTableTypes() {
		$this->connection->exec('DROP TABLE IF EXISTS foo');
		$this->connection->exec('CREATE TABLE foo (id integer, name text)');
		Assert::same(
			[
				['name' => 'first', 'id' => 1],
				['name' => 'second', 'id' => 2],
			],
			(new Output\PgArrayToArray(
				$this->connection,
				'{"(1,first)","(2,second)"}',
				'_foo',
				new Output\FakeConversion('foo')
			))->value()
		);
	}

	public function testConversionOfArrayOfTableTypesWithComplexColumn() {
		$this->connection->exec('DROP TABLE IF EXISTS foo');
		$this->connection->exec('CREATE TABLE foo (id integer, coordinates point)');
		Assert::same(
			[
				['coordinates' => ['x' => 50.5, 'y' => 60.5], 'id' => 1],
				['coordinates' => ['x' => 50.6, 'y' => 60.6], 'id' => 2],
			],
			(new Output\PgArrayToArray(
				$this->connection,
				'{"(1,\"(50.5,60.5)\")","(2,\"(50.6,60.6)\")"}',
				'_foo',
				new Output\FakeConversion('foo')
			))->value()
		);
	}
}

(new PgArrayToArray())->run();