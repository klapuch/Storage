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
	public function testCastingToHStore() {
		Assert::same(
			['list' => ['name' => 'Dom', 'race' => 'human']],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => 'name=>Dom,race=>human']]),
				['list' => 'hstore']
			))->row()
		);
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

	public function testCastingCompoundType() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			['list' => ['name' => 'Dom', 'race' => 'human']],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => '(Dom,human)']]),
				['list' => 'person']
			))->row()
		);
	}

	public function testCastingCaseInsensitiveCompoundType() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			['list' => ['name' => 'Dom', 'race' => 'human']],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => '(Dom,human)']]),
				['list' => 'PERSON']
			))->row()
		);
	}

	public function testCastingArrayOfComposedTypes() {
		(new Storage\ParameterizedQuery($this->database, 'DROP TYPE IF EXISTS person'))->execute();
		(new Storage\ParameterizedQuery($this->database, 'CREATE TYPE person AS (name TEXT, race TEXT)'))->execute();
		Assert::same(
			[
				['list' => [
					[
						'name' => 'Dom',
						'race' => 'human',
					],
					[
						'name' => 'Dan',
						'race' => 'master',
					],
				],
				],
			],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => '{"(Dom,human)","(Dan,master)"}']]),
				['list' => 'person[]']
			))->rows()
		);
	}

	public function testCastingToArray() {
		Assert::same(
			['list' => [1, 2, 3]],
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([['list' => '{1, 2, 3}']]),
				['list' => 'integer[]']
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

	public function testThrowingOnUnsupportedType() {
		Assert::exception(function() {
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([]),
				['list' => 'foo', 'list2' => 'bar']
			))->row();
		}, \UnexpectedValueException::class, 'Following types are not supported: "foo, bar"');
		Assert::exception(function() {
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([]),
				['list' => 'foo', 'list2' => 'bar']
			))->rows();
		}, \UnexpectedValueException::class, 'Following types are not supported: "foo, bar"');
		Assert::exception(function() {
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([]),
				['list' => 'foo', 'list2' => 'bar']
			))->execute();
		}, \UnexpectedValueException::class, 'Following types are not supported: "foo, bar"');
		Assert::exception(function() {
			(new Storage\TypedQuery(
				$this->database,
				new Storage\FakeQuery([]),
				['list' => 'foo', 'list2' => 'bar']
			))->field();
		}, \UnexpectedValueException::class, 'Following types are not supported: "foo, bar"');
	}
}


(new TypedQuery())->run();