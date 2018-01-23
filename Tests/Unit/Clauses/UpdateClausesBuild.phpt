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

final class UpdateClausesBuild extends Tester\TestCase {
	public function testMultipleSet() {
		$clauses = (new Storage\Clauses\AnsiUpdate('world'))
			->set(['mood' => '?', 'age' => '?']);
		Assert::same('UPDATE world SET mood = ?, age = ?', $clauses->sql());
	}

	public function testMultipleWhere() {
		$clauses = (new Storage\Clauses\AnsiUpdate('world'))
			->set(['mood' => '?'])
			->where('age > 20');
		Assert::same('UPDATE world SET mood = ? WHERE age > 20', $clauses->sql());
	}

	public function testAppendingDifferentSet() {
		$clauses = (new Storage\Clauses\PgSet(
			(new Storage\Clauses\AnsiUpdate('world'))->set(['mood' => '?', 'age' => '?']),
			['name' => 'Dom']
		))->where('age > 20');
		Assert::same("UPDATE world SET mood = ?, age = ?, name = 'Dom' WHERE age > 20", $clauses->sql());
	}
}

(new UpdateClausesBuild())->run();