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

final class PgSet extends Tester\TestCase {
	public function testValuesAsExpressions() {
		$clauses = new Storage\Clauses\PgSet(
			new Storage\Clauses\FakeClause(),
			[
				'name' => 'foo',
				'age' => '20',
			]
		);
		Assert::same(
			" SET name = 'foo', age = '20'",
			$clauses->sql()
		);
	}
}

(new PgSet())->run();