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

final class InsertIntoClausesBuild extends Tester\TestCase {
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
}

(new InsertIntoClausesBuild())->run();