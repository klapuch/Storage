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
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, age INTEGER, cool BOOLEAN)'))->execute();
		Assert::same(
			['name' => 'Dom', 'age' => 21, 'cool' => true],
			(new Storage\PgRowToTypedArray(
				new Storage\FakeConversion(['name' => 'Dom', 'age' => '21', 'cool' => 't']),
				'person',
				$this->database
			))->value()
		);
	}

	public function testTypingCorrectlyNumberInText() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT)'))->execute();
		Assert::same(
			['name' => '123'],
			(new Storage\PgRowToTypedArray(
				new Storage\FakeConversion(['name' => '123']),
				'person',
				$this->database
			))->value()
		);
	}

	public function testPassingWithCaseInsensitiveType() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, age INTEGER, cool BOOLEAN)'))->execute();
		Assert::same(
			['name' => 'Dom', 'age' => 21, 'cool' => true],
			(new Storage\PgRowToTypedArray(
				new Storage\FakeConversion(['name' => 'Dom', 'age' => '21', 'cool' => 't']),
				'PERSON',
				$this->database
			))->value()
		);
	}
}

(new PgRowToTypedArray())->run();