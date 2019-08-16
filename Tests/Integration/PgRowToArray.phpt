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
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgRowToArray($this->connection, '(Dom,human)', 'person_type'))->value()
		);
	}

	public function testThrowingOnUnknownType() {
		$ex = Assert::exception(function() {
			(new Storage\PgRowToArray($this->connection, '(Dom,human)', 'xxx'))->value();
		}, \UnexpectedValueException::class, 'Type "xxx" does not exist');
		Assert::type(\PDOException::class, $ex->getPrevious());
	}

	public function testThrowingOnNotProperUseOfRow() {
		$ex = Assert::exception(function() {
			(new Storage\PgRowToArray($this->connection, '(Dom,human,idk)', 'person_type'))->value();
		}, \UnexpectedValueException::class, 'Type "person_type" only exists as (name, race)');
		Assert::type(\PDOException::class, $ex->getPrevious());
	}

	public function testThrowingOnNotProperUseOfRowAsTable() {
		$ex = Assert::exception(function() {
			(new Storage\PgRowToArray($this->connection, '(Dom,human,idk)', 'person_table'))->value();
		}, \UnexpectedValueException::class, 'Type "person_table" only exists as (name, race)');
		Assert::type(\PDOException::class, $ex->getPrevious());
	}

	public function testThrowingOnNotProperUseOfRowWithCaseInsensitiveMatch() {
		Assert::exception(function() {
			(new Storage\PgRowToArray($this->connection, '(Dom,human,idk)', 'PERSON_TYPE'))->value();
		}, \UnexpectedValueException::class, 'Type "PERSON_TYPE" only exists as (name, race)');
		Assert::exception(function() {
			(new Storage\PgRowToArray($this->connection, '(Dom,human,idk)', 'person_type'))->value();
		}, \UnexpectedValueException::class, 'Type "person_type" only exists as (name, race)');
	}

	public function testConvertingArrayOfRowsArray() {
		Assert::same(
			[
				['name' => 'Dom', 'race' => 'human'],
				['name' => 'Dan', 'race' => 'master'],
			],
			(new Storage\PgRowToArray($this->connection, '{"(Dom,human)","(Dan,master)"}', 'person_type[]'))->value()
		);
	}

	public function testConvertingTableTypeToArray() {
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgRowToArray($this->connection, '(Dom,human)', 'person_table'))->value()
		);
	}

	public function testConvertingNestedType() {
		Assert::same(
			['length' => ['value' => 10, 'unit' => 'mm'], 'name' => 'Dom'],
			(new Storage\PgRowToArray($this->connection, '("(10,mm)",Dom)', 'person_table2'))->value()
		);
	}
}

(new PgRowToArray())->run();