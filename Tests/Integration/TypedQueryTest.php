<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class TypedQueryTest extends TestCase\PostgresDatabase {
	public function testCastingCommonScalarValues(): void {
		(new Storage\TypedQuery($this->connection, 'DROP TABLE IF EXISTS scalars CASCADE'))->execute();
		(new Storage\TypedQuery($this->connection, 'CREATE TABLE scalars (name text, age smallint, good boolean, bad boolean, id integer)'))->execute();
		(new Storage\TypedQuery(
			$this->connection,
			'INSERT INTO scalars (name, age, good, bad, id) VALUES (?, ?, ?, ?, ?)',
			['Dom', 21, true, false, 123456789],
		))->execute();
		Assert::same(
			['name' => 'Dom', 'age' => 21, 'good' => true, 'bad' => false, 'id' => 123456789],
			(new Storage\TypedQuery($this->connection, 'SELECT * FROM scalars'))->row(),
		);
	}

	public function testCastingPgTypes(): void {
		(new Storage\TypedQuery($this->connection, 'DROP TABLE IF EXISTS pg_types CASCADE'))->execute();
		(new Storage\TypedQuery($this->connection, 'CREATE TABLE pg_types (list jsonb)'))->execute();
		(new Storage\TypedQuery(
			$this->connection,
			"INSERT INTO pg_types (list) VALUES (array_to_json(ARRAY['a', 'b']::text[]))",
		))->execute();
		Assert::same(
			['list' => ['a', 'b']],
			(new Storage\TypedQuery($this->connection, 'SELECT * FROM pg_types'))->row(),
		);
	}

	public function testKeepingNull(): void {
		(new Storage\TypedQuery($this->connection, 'DROP TABLE IF EXISTS pg_types CASCADE'))->execute();
		(new Storage\TypedQuery($this->connection, 'CREATE TABLE pg_types (list jsonb, age int4range, gps point)'))->execute();
		(new Storage\TypedQuery($this->connection, 'INSERT INTO pg_types (list, age, gps) VALUES (NULL, NULL, NULL)'))->execute();
		Assert::same(
			['list' => null, 'age' => null, 'gps' => null],
			(new Storage\TypedQuery($this->connection, 'SELECT * FROM pg_types'))->row(),
		);
	}

	public function testCastingSingleField(): void {
		(new Storage\TypedQuery($this->connection, 'DROP TABLE IF EXISTS scalars CASCADE'))->execute();
		(new Storage\TypedQuery($this->connection, 'CREATE TABLE scalars (name text, age smallint, good boolean, bad boolean, id integer)'))->execute();
		(new Storage\TypedQuery(
			$this->connection,
			'INSERT INTO scalars (name, age, good, bad, id) VALUES (?, ?, ?, ?, ?)',
			['Dom', 21, true, false, 123456789],
		))->execute();
		Assert::same(123456789, (new Storage\TypedQuery($this->connection, 'SELECT id FROM scalars'))->field());
		Assert::true((new Storage\TypedQuery($this->connection, 'SELECT good FROM scalars'))->field());
		Assert::same('Dom', (new Storage\TypedQuery($this->connection, 'SELECT name FROM scalars'))->field());
	}

	public function testPassingWithEmptySet(): void {
		(new Storage\TypedQuery($this->connection, 'DROP TABLE IF EXISTS scalars CASCADE'))->execute();
		(new Storage\TypedQuery($this->connection, 'CREATE TABLE scalars (name text, age smallint, good boolean, bad boolean, id integer)'))->execute();
		Assert::same([], (new Storage\TypedQuery($this->connection, 'SELECT * FROM scalars'))->row());
		Assert::same([], (new Storage\TypedQuery($this->connection, 'SELECT * FROM scalars'))->rows());
		Assert::false((new Storage\TypedQuery($this->connection, 'SELECT * FROM scalars'))->field());
	}
}


(new TypedQueryTest())->run();
