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

final class AnsiMultiInsertInto extends Tester\TestCase {
	public function testInsertingMultipleValues() {
		$clauses = new Storage\Clauses\AnsiMultiInsertInto(
			'world',
			[['name' => '?', 'age' => ':age'], ['name' => '?', 'age' => ':age2']]
		);
		Assert::same(
			'INSERT INTO world (name, age) VALUES (?, :age), (?, :age2)',
			$clauses->sql()
		);
	}
}

(new AnsiMultiInsertInto())->run();