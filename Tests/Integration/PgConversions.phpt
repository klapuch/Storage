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

final class PgConversions extends TestCase\PostgresDatabase {
	public function testCastingToHStore() {
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgConversions($this->database, 'name=>Dom,race=>human', ['HSTORE']))->value()
		);
	}

	public function testCastingToHStoreAsCaseInsensitive() {
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgConversions($this->database, 'name=>Dom,race=>human', ['hstore']))->value()
		);
	}

	public function testKeepingUnknownType() {
		Assert::same(
			'name=>Dom,race=>human',
			(new Storage\PgConversions($this->database, 'name=>Dom,race=>human', ['FOO']))->value()
		);
	}

	public function testCastingToArray() {
		Assert::same(
			[1, 2, 3],
			(new Storage\PgConversions($this->database, '{1, 2, 3}', ['integer[]']))->value()
		);
	}

	public function testCastingCompoundType() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgConversions($this->database, '(Dom,human)', ['person']))->value()
		);
	}

	public function testCastingAsCaseInsensitiveCompoundType() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgConversions($this->database, '(Dom,human)', ['PERson']))->value()
		);
	}

	public function testCastingArrayOfCompoundTypes() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			[
				[
					'name' => 'Dom',
					'race' => 'human',
				],
				[
					'name' => 'Dan',
					'race' => 'master',
				],
			],
			(new Storage\PgConversions(
				$this->database,
				'{"(Dom,human)","(Dan,master)"}',
				['person[]']
			))->value()
		);
	}

	public function testCastingByDefaultToPhpValuesForCompoundType() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, age INTEGER, cool BOOLEAN)'))->execute();
		Assert::equal(
			['name' => 'Dom', 'age' => 21, 'cool' => true],
			(new Storage\PgConversions($this->database, '(Dom,21,t)', ['person']))->value()
		);
	}

	public function testSpecifyingTypesForCompoundType() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, age INTEGER, cool BOOLEAN)'))->execute();
		Assert::equal(
			['name' => 'Dom', 'age' => 21, 'cool' => 't'],
			(new Storage\PgConversions($this->database, '(Dom,21,t)', ['person' => ['cool' => 'string']]))->value()
		);
	}
}

(new PgConversions())->run();