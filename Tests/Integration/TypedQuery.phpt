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
	public function testCastingSimpleTypeForRow() {
		Assert::same(
			['list' => ['name' => 'Dom', 'race' => 'human']],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => 'name=>Dom,race=>human']]),
				['list' => 'hstore']
			))->row()
		);
	}

	public function testCastingSimpleTypeForRows() {
		Assert::same(
			[
				['list' => ['name' => 'Dom', 'race' => 'human']],
				['list' => ['name' => 'Dan', 'race' => 'master']],
			],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => 'name=>Dom,race=>human'], ['list' => 'name=>Dan,race=>master']]),
				['list' => 'hstore']
			))->rows()
		);
	}

	public function testCastingCompoundType() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			['list' => ['name' => 'Dom', 'race' => 'master']],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => '(Dom,master)']]),
				['list' => 'person']
			))->row()
		);
	}

	public function testCastingToMultipleTypesAtTime() {
		Assert::same(
			['list' => [1, 2, 3], 'list2' => ['name' => 'Dom', 'race' => 'human']],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => '{1, 2, 3}', 'list2' => 'name=>Dom,race=>human']]),
				[
					'list' => 'integer[]',
					'list2' => 'hstore',
				]
			))->row()
		);
	}

	public function testCaseInsensitiveMatch() {
		Assert::noError(function() {
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => '{1, 2, 3}', 'list2' => 'name=>Dom,race=>human']]),
				[
					'list' => 'INTEGER[]',
					'list2' => 'HSTORE',
				]
			))->row();
		});
	}

	public function testCastingFieldAsFirstValue() {
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery(['list' => 'name=>Dom,race=>human']),
				['list' => 'hstore']
			))->field()
		);
	}

	public function testIgnoringOnUnsupportedType() {
		Assert::noError(function() {
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => '{1, 2, 3}']]),
				['list' => 'foo']
			))->row();
		});
		Assert::noError(function() {
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => '{1, 2, 3}']]),
				['list' => 'foo']
			))->rows();
		});
		Assert::noError(function() {
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => '{1, 2, 3}']]),
				['list' => 'foo']
			))->execute();
		});
		Assert::noError(function() {
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery(['list' => '{1, 2, 3}']),
				['list' => 'foo']
			))->field();
		});
	}

	public function testCastingCompoundTypeToPhpType() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, age INTEGER, cool BOOLEAN)'))->execute();
		Assert::equal(
			['list' => ['name' => 'Dom', 'age' => 21, 'cool' => true]],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => '(Dom,21,t)']]),
				['list' => 'person']
			))->row()
		);
	}

	public function testAcceptingOnlyScalars() {
		Assert::same(
			['list' => 'name=>Dom,race=>human', 'bla' => []],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => 'name=>Dom,race=>human', 'bla' => []]]),
				['list' => 'hstore']
			))->row()
		);
	}
}


(new TypedQuery())->run();