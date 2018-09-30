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
		(new Storage\NativeQuery($this->connection, 'DROP TYPE IF EXISTS person CASCADE'))->execute();
		(new Storage\NativeQuery($this->connection, 'CREATE TYPE person AS (name TEXT, age INTEGER, cool BOOLEAN)'))->execute();
		Assert::same(
			['name' => 'Dom', 'age' => 21, 'cool' => true],
			(new Storage\PgRowToTypedArray(
				$this->connection,
				'(Dom,21,t)',
				'person',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testTypingCorrectlyNumberInText() {
		(new Storage\NativeQuery($this->connection, 'DROP TYPE IF EXISTS person CASCADE'))->execute();
		(new Storage\NativeQuery($this->connection, 'CREATE TYPE person AS (name TEXT)'))->execute();
		Assert::same(
			['name' => '123'],
			(new Storage\PgRowToTypedArray(
				$this->connection,
				'(123)',
				'person',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testPassingWithNull() {
		(new Storage\NativeQuery($this->connection, 'DROP TYPE IF EXISTS person CASCADE'))->execute();
		(new Storage\NativeQuery($this->connection, 'CREATE TYPE person AS (name TEXT)'))->execute();
		Assert::same(
			['name' => null],
			(new Storage\PgRowToTypedArray(
				$this->connection,
				'()',
				'person',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testKeepingAlreadyConvertedValue() {
		(new Storage\NativeQuery($this->connection, 'DROP TYPE IF EXISTS length CASCADE'))->execute();
		(new Storage\NativeQuery($this->connection, 'CREATE TYPE length AS (value INTEGER, unit TEXT)'))->execute();
		(new Storage\NativeQuery($this->connection, 'DROP TABLE IF EXISTS person_table CASCADE'))->execute();
		(new Storage\NativeQuery($this->connection, 'CREATE TABLE person_table (name TEXT, age INTEGER, cool BOOLEAN, length length)'))->execute();
		Assert::same(
			[
				'name' => 'Dom',
				'age' => 21,
				'cool' => true,
				'length' => ['value' => 10, 'unit' => 'mm'],
			],
			(new Storage\PgRowToTypedArray(
				$this->connection,
				'(Dom,21,t,"(10,mm)")',
				'person_table',
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