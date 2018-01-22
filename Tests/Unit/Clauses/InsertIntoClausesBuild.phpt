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
	public function testInsertingByTypes() {
		$clauses = new Storage\Clauses\PgInsertInto(
			'world',
			[
				'name' => 'Dom',
				'age' => 25,
				'cool' => true,
				'not_cool' => false,
			]
		);
		Assert::same(
			"INSERT INTO world (name, age, cool, not_cool) VALUES ('Dom', 25, true, false)",
			$clauses->sql()
		);
	}

	public function testInsertingWithReturning() {
		$clauses = (new Storage\Clauses\PgInsertInto('world', ['name' => 'Dom']))
			->returning(['name', '*']);
		Assert::same(
			"INSERT INTO world (name) VALUES ('Dom') RETURNING name, *",
			$clauses->sql()
		);
	}
}

(new InsertIntoClausesBuild())->run();