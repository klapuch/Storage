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
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS person CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE person AS (name TEXT, age INTEGER, cool BOOLEAN)'))->execute();
		Assert::same(
			['name' => 'Dom', 'age' => 21, 'cool' => true],
			(new Storage\PgRowToTypedArray(
				'(Dom,21,t)',
				'person',
				$this->database,
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testTypingCorrectlyNumberInText() {
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS person CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE person AS (name TEXT)'))->execute();
		Assert::same(
			['name' => '123'],
			(new Storage\PgRowToTypedArray(
				'(123)',
				'person',
				$this->database,
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testPassingWithNull() {
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS person CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE person AS (name TEXT)'))->execute();
		Assert::same(
			['name' => null],
			(new Storage\PgRowToTypedArray(
				'()',
				'person',
				$this->database,
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testKeepingAlreadyConvertedValue() {
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS length CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE length AS (value INTEGER, unit TEXT)'))->execute();
		(new Storage\NativeQuery($this->database, 'DROP TABLE IF EXISTS person_table CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TABLE person_table (name TEXT, age INTEGER, cool BOOLEAN, length length)'))->execute();
		Assert::same(
			[
				'name' => 'Dom',
				'age' => 21,
				'cool' => true,
				'length' => ['value' => 10, 'unit' => 'mm'],
			],
			(new Storage\PgRowToTypedArray(
				'(Dom,21,t,"(10,mm)")',
				'person_table',
				$this->database,
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testDelegatingWithUnknownType() {
		Assert::same(
			'foo',
			(new Storage\PgRowToTypedArray(
				'(bar)',
				'unknown_type',
				$this->database,
				new Storage\FakeConversion('foo')
			))->value()
		);
	}
}

(new PgRowToTypedArray())->run();