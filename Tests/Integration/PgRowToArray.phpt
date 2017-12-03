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

final class PgRowToArray extends TestCase\PostgresDatabase {
	public function testConvertingToArray() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgRowToArray($this->database, '(Dom,human)', 'person'))->value()
		);
	}

	public function testThrowingOnUnknownType() {
		$ex = Assert::exception(function() {
			(new Storage\PgRowToArray($this->database, '(Dom,human)', 'xxx'))->value();
		}, \UnexpectedValueException::class, 'Type "xxx" does not exist');
		Assert::type(\PDOException::class, $ex->getPrevious());
	}

	public function testThrowingOnNotProperUseOfRow() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		$ex = Assert::exception(function() {
			(new Storage\PgRowToArray($this->database, '(Dom,human,idk)', 'person'))->value();
		}, \UnexpectedValueException::class, 'Type "person" only exists as (name, race)');
		Assert::type(\PDOException::class, $ex->getPrevious());
	}

	public function testThrowingOnNotProperUseOfRowAsTable() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TABLE IF EXISTS person_table'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TABLE person_table (name TEXT, race TEXT)'))->execute();
		$ex = Assert::exception(function() {
			(new Storage\PgRowToArray($this->database, '(Dom,human,idk)', 'person_table'))->value();
		}, \UnexpectedValueException::class, 'Type "person_table" only exists as (name, race)');
		Assert::type(\PDOException::class, $ex->getPrevious());
	}

	public function testThrowingOnNotProperUseOfRowWithCaseInsensitiveMatch() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::exception(function() {
			(new Storage\PgRowToArray($this->database, '(Dom,human,idk)', 'PERSON'))->value();
		}, \UnexpectedValueException::class, 'Type "PERSON" only exists as (name, race)');
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE PERSON AS (name TEXT, race TEXT)'))->execute();
		Assert::exception(function() {
			(new Storage\PgRowToArray($this->database, '(Dom,human,idk)', 'person'))->value();
		}, \UnexpectedValueException::class, 'Type "person" only exists as (name, race)');
	}

	public function testConvertingArrayOfRowsArray() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			[
				['name' => 'Dom', 'race' => 'human'],
				['name' => 'Dan', 'race' => 'master'],
			],
			(new Storage\PgRowToArray($this->database, '{"(Dom,human)","(Dan,master)"}', 'person[]'))->value()
		);
	}

	public function testAllowingNull() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::null((new Storage\PgRowToArray($this->database, null, 'person'))->value());
	}

	public function testConvertingTableTypeToArray() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TABLE IF EXISTS person_table'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TABLE person_table (name TEXT, race TEXT)'))->execute();
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgRowToArray($this->database, '(Dom,human)', 'person_table'))->value()
		);
	}
}

(new PgRowToArray())->run();