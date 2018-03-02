<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Klapuch\Storage\Unit\Clauses;

use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class AnsiInsertInto extends Tester\TestCase {
	public function testInsertingMultipleValues() {
		$clauses = new Storage\Clauses\AnsiInsertInto(
			'world',
			['name' => '?', 'age' => ':age']
		);
		Assert::same(
			'INSERT INTO world (name, age) VALUES (?, :age)',
			$clauses->sql()
		);
	}

	public function testInsertingWithReturning() {
		$clauses = (new Storage\Clauses\AnsiInsertInto('world', ['name' => '?']))
			->returning(['name', '*']);
		Assert::same(
			'INSERT INTO world (name) VALUES (?) RETURNING name, *',
			$clauses->sql()
		);
	}

	public function testOnConflictUpdate() {
		$clauses = (new Storage\Clauses\AnsiInsertInto('world', ['name' => '?']))
			->onConflict()
			->doUpdate(['name' => '?']);
		Assert::same(
			'INSERT INTO world (name) VALUES (?) ON CONFLICT DO UPDATE SET name = ?',
			$clauses->sql()
		);
	}

	public function testOnConflictTargetUpdate() {
		$clauses = (new Storage\Clauses\AnsiInsertInto('world', ['name' => '?']))
			->onConflict(['foo', 'bar'])
			->doUpdate(['name' => '?']);
		Assert::same(
			'INSERT INTO world (name) VALUES (?) ON CONFLICT (foo, bar) DO UPDATE SET name = ?',
			$clauses->sql()
		);
	}

	public function testOnConflictDoNothing() {
		$clauses = (new Storage\Clauses\AnsiInsertInto('world', ['name' => '?']))
			->onConflict()
			->doNothing();
		Assert::same(
			'INSERT INTO world (name) VALUES (?) ON CONFLICT DO NOTHING',
			$clauses->sql()
		);
	}
}

(new AnsiInsertInto())->run();