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
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS person CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
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
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS person CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		$ex = Assert::exception(function() {
			(new Storage\PgRowToArray($this->database, '(Dom,human,idk)', 'person'))->value();
		}, \UnexpectedValueException::class, 'Type "person" only exists as (name, race)');
		Assert::type(\PDOException::class, $ex->getPrevious());
	}

	public function testThrowingOnNotProperUseOfRowAsTable() {
		(new Storage\NativeQuery($this->database, 'DROP TABLE IF EXISTS person_table CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TABLE person_table (name TEXT, race TEXT)'))->execute();
		$ex = Assert::exception(function() {
			(new Storage\PgRowToArray($this->database, '(Dom,human,idk)', 'person_table'))->value();
		}, \UnexpectedValueException::class, 'Type "person_table" only exists as (name, race)');
		Assert::type(\PDOException::class, $ex->getPrevious());
	}

	public function testThrowingOnNotProperUseOfRowWithCaseInsensitiveMatch() {
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS person CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::exception(function() {
			(new Storage\PgRowToArray($this->database, '(Dom,human,idk)', 'PERSON'))->value();
		}, \UnexpectedValueException::class, 'Type "PERSON" only exists as (name, race)');
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS person CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE PERSON AS (name TEXT, race TEXT)'))->execute();
		Assert::exception(function() {
			(new Storage\PgRowToArray($this->database, '(Dom,human,idk)', 'person'))->value();
		}, \UnexpectedValueException::class, 'Type "person" only exists as (name, race)');
	}

	public function testConvertingArrayOfRowsArray() {
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS person CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			[
				['name' => 'Dom', 'race' => 'human'],
				['name' => 'Dan', 'race' => 'master'],
			],
			(new Storage\PgRowToArray($this->database, '{"(Dom,human)","(Dan,master)"}', 'person[]'))->value()
		);
	}

	public function testConvertingTableTypeToArray() {
		(new Storage\NativeQuery($this->database, 'DROP TABLE IF EXISTS person_table CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TABLE person_table (name TEXT, race TEXT)'))->execute();
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgRowToArray($this->database, '(Dom,human)', 'person_table'))->value()
		);
	}

	public function testConvertingNestedType() {
		(new Storage\NativeQuery($this->database, 'DROP TYPE IF EXISTS length CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TYPE length AS (value INTEGER, unit TEXT)'))->execute();
		(new Storage\NativeQuery($this->database, 'DROP TABLE IF EXISTS person_table CASCADE'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TABLE person_table (length length, name TEXT)'))->execute();
		Assert::same(
			['length' => ['value' => 10, 'unit' => 'mm'], 'name' => 'Dom'],
			(new Storage\PgRowToArray($this->database, '("(10,mm)",Dom)', 'person_table'))->value()
		);
	}
}

(new PgRowToArray())->run();