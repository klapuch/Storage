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

final class TypedQuery extends TestCase\PostgresDatabase {
	public function testCastingCommonScalarValues() {
		(new Storage\TypedQuery($this->connection, 'DROP TABLE IF EXISTS scalars CASCADE'))->execute();
		(new Storage\TypedQuery($this->connection, 'CREATE TABLE scalars (name text, age smallint, good boolean, bad boolean, id integer)'))->execute();
		(new Storage\TypedQuery(
			$this->connection,
			'INSERT INTO scalars (name, age, good, bad, id) VALUES
			(?, ?, ?, ?, ?)',
			['Dom', 21, true, false, 123456789]
		))->execute();
		Assert::same(
			['name' => 'Dom', 'age' => 21, 'good' => true, 'bad' => false, 'id' => 123456789],
			(new Storage\TypedQuery($this->connection, 'SELECT * FROM scalars'))->row()
		);
	}

	public function testCastingPgTypes() {
		(new Storage\TypedQuery($this->connection, 'DROP TABLE IF EXISTS pg_types CASCADE'))->execute();
		(new Storage\TypedQuery($this->connection, 'CREATE TABLE pg_types (list hstore, age int4range, gps point)'))->execute();
		(new Storage\TypedQuery(
			$this->connection,
			'INSERT INTO pg_types (list, age, gps) VALUES
				(hstore(?, ?), int4range(21, 25), point(40.5,20.6))',
			['name', 'Dom']
		))->execute();
		Assert::same(
			['list' => ['name' => 'Dom'], 'age' => [21, 25, '[', ')'], 'gps' => ['x' => 40.5, 'y' => 20.6]],
			(new Storage\TypedQuery($this->connection, 'SELECT * FROM pg_types'))->row()
		);
	}

	public function testKeepingNull() {
		(new Storage\TypedQuery($this->connection, 'DROP TABLE IF EXISTS pg_types CASCADE'))->execute();
		(new Storage\TypedQuery($this->connection, 'CREATE TABLE pg_types (list hstore, age int4range, gps point)'))->execute();
		(new Storage\TypedQuery($this->connection, 'INSERT INTO pg_types (list, age, gps) VALUES (NULL, NULL, NULL)'))->execute();
		Assert::same(
			['list' => null, 'age' => null, 'gps' => null],
			(new Storage\TypedQuery($this->connection, 'SELECT * FROM pg_types'))->row()
		);
	}

	public function testCastingBatchOfTypes() {
		(new Storage\TypedQuery($this->connection, 'DROP TABLE IF EXISTS pg_types CASCADE'))->execute();
		(new Storage\TypedQuery($this->connection, 'CREATE TABLE pg_types (list hstore, age int4range, gps point)'))->execute();
		(new Storage\TypedQuery(
			$this->connection,
			'INSERT INTO pg_types (list, age, gps) VALUES
				(hstore(?, ?), int4range(21, 25), point(40.5,20.6)),
				(hstore(?, ?), int4range(10, 20), point(10,15))',
			['name', 'Dom', 'name', 'Dell']
		))->execute();
		Assert::same(
			[
				['list' => ['name' => 'Dom'], 'age' => [21, 25, '[', ')'], 'gps' => ['x' => 40.5, 'y' => 20.6]],
				['list' => ['name' => 'Dell'], 'age' => [10, 20, '[', ')'], 'gps' => ['x' => 10.0, 'y' => 15.0]],
			],
			(new Storage\TypedQuery($this->connection, 'SELECT * FROM pg_types'))->rows()
		);
	}

	public function testCastingSingleField() {
		(new Storage\TypedQuery($this->connection, 'DROP TABLE IF EXISTS scalars CASCADE'))->execute();
		(new Storage\TypedQuery($this->connection, 'CREATE TABLE scalars (name text, age smallint, good boolean, bad boolean, id integer)'))->execute();
		(new Storage\TypedQuery(
			$this->connection,
			'INSERT INTO scalars (name, age, good, bad, id) VALUES
			(?, ?, ?, ?, ?)',
			['Dom', 21, true, false, 123456789]
		))->execute();
		Assert::same(123456789, (new Storage\TypedQuery($this->connection, 'SELECT id FROM scalars'))->field());
		Assert::true((new Storage\TypedQuery($this->connection, 'SELECT good FROM scalars'))->field());
		Assert::same('Dom', (new Storage\TypedQuery($this->connection, 'SELECT name FROM scalars'))->field());
	}

	public function testPassingWithEmptySet() {
		(new Storage\TypedQuery($this->connection, 'DROP TABLE IF EXISTS scalars CASCADE'))->execute();
		(new Storage\TypedQuery($this->connection, 'CREATE TABLE scalars (name text, age smallint, good boolean, bad boolean, id integer)'))->execute();
		Assert::same([], (new Storage\TypedQuery($this->connection, 'SELECT * FROM scalars'))->row());
		Assert::same([], (new Storage\TypedQuery($this->connection, 'SELECT * FROM scalars'))->rows());
		Assert::false((new Storage\TypedQuery($this->connection, 'SELECT * FROM scalars'))->field());
	}
}


(new TypedQuery())->run();