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

final class AnsiSet extends Tester\TestCase {
	public function testValuesForPreparedStatement() {
		$clauses = new Storage\Clauses\AnsiSet(
			new Storage\Clauses\FakeClause(),
			[
				'name' => '?',
				'age' => ':age',
			]
		);
		Assert::same(
			' SET name = ?, age = :age',
			$clauses->sql()
		);
	}
}

(new AnsiSet())->run();