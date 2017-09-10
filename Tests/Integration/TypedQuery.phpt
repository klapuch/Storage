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

	/**
	 * @throws \UnexpectedValueException Following types are not supported: "foo"
	 */
	public function testThrowingOnUnsupportedType() {
		(new Storage\TypedQuery(
			$this->database,
			new Storage\FakeQuery([]),
			['list' => 'foo']
		))->row();
	}
}


(new TypedQuery())->run();