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
	public function testCasting() {
		Assert::equal(
			(new Storage\PgTimestamptzRangeToArray($this->database, '[2004-10-19 10:23:54.20+02,2005-10-19 10:23:54.20+02)'))->value(),
			(new Storage\PgConversions($this->database, '[2004-10-19 10:23:54.20+02,2005-10-19 10:23:54.20+02)', 'tstzrange'))->value()
		);
		Assert::same(
			(new Storage\PgHStoreToArray($this->database, 'name=>Dom,race=>human'))->value(),
			(new Storage\PgConversions($this->database, 'name=>Dom,race=>human', 'hstore'))->value()
		);
		Assert::same(
			(new Storage\PgIntRangeToArray($this->database, '[10,20)'))->value(),
			(new Storage\PgConversions($this->database, '[10,20)', 'int4range'))->value()
		);
		Assert::same(
			(new Storage\PgPointToArray($this->database, '(10.2,10.3)'))->value(),
			(new Storage\PgConversions($this->database, '(10.2,10.3)', 'point'))->value()
		);
	}

	public function testCaseInsensitiveCasting() {
		Assert::equal(
			(new Storage\PgTimestamptzRangeToArray($this->database, '[2004-10-19 10:23:54.20+02,2005-10-19 10:23:54.20+02)'))->value(),
			(new Storage\PgConversions($this->database, '[2004-10-19 10:23:54.20+02,2005-10-19 10:23:54.20+02)', 'TSTZRANGE'))->value()
		);
		Assert::same(
			(new Storage\PgHStoreToArray($this->database, 'name=>Dom,race=>human'))->value(),
			(new Storage\PgConversions($this->database, 'name=>Dom,race=>human', 'HSTORE'))->value()
		);
		Assert::same(
			(new Storage\PgIntRangeToArray($this->database, '[10,20)'))->value(),
			(new Storage\PgConversions($this->database, '[10,20)', 'INT4RANGE'))->value()
		);
		Assert::same(
			(new Storage\PgPointToArray($this->database, '(10.2,10.3)'))->value(),
			(new Storage\PgConversions($this->database, '(10.2,10.3)', 'POINT'))->value()
		);
	}

	public function testKeepingUnknownType() {
		Assert::same(
			'name=>Dom,race=>human',
			(new Storage\PgConversions($this->database, 'name=>Dom,race=>human', 'FOO'))->value()
		);
	}

	public function testCastingToArray() {
		Assert::same(
			[1, 2, 3],
			(new Storage\PgConversions($this->database, '{1, 2, 3}', 'integer[]'))->value()
		);
	}

	public function testCastingCompoundType() {
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgConversions($this->database, '(Dom,human)', 'person'))->value()
		);
	}

	public function testCastingAsCaseInsensitiveCompoundType() {
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgConversions($this->database, '(Dom,human)', 'PERson'))->value()
		);
	}

	public function testCastingCompoundTypeAsTable() {
		(new Storage\NativeQuery($this->database, 'DROP TABLE IF EXISTS person_table'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TABLE person_table (name TEXT, age INTEGER, cool BOOLEAN)'))->execute();
		Assert::equal(
			['name' => 'Dom', 'age' => 21, 'cool' => true],
			(new Storage\PgConversions($this->database, '(Dom,21,t)', 'person_table'))->value()
		);
	}

	public function testAllowingOriginalToBeNull() {
		Assert::same(
			null,
			(new Storage\PgConversions($this->database, null, 'int4range'))->value()
		);
	}
}

(new PgConversions())->run();