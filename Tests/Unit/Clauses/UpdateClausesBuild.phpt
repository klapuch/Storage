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
			->set(['mood' => 'good', 'age' => 25]);
		Assert::same("UPDATE world SET mood = 'good', age = '25'", $clauses->sql());
	}

	public function testMultipleWhere() {
		$clauses = (new Storage\Clauses\AnsiUpdate('world'))
			->set(['mood' => 'good'])
			->where('age > 20');
		Assert::same("UPDATE world SET mood = 'good' WHERE age > 20", $clauses->sql());
	}
}

(new UpdateClausesBuild())->run();