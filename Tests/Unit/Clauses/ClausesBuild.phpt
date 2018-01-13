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

final class ClausesBuild extends Tester\TestCase {
	public function testBuildFromThenWhere() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->where('age > 20')
			->groupBy(['firstname'])
			->having('firstname > 10')
			->orderBy(['firstname' => 'DESC'])
			->limit(10)
			->offset(100);
		Assert::same('SELECT firstname, lastname FROM person WHERE age > 20 GROUP BY firstname HAVING firstname > 10 ORDER BY firstname DESC LIMIT 10 OFFSET 100', $clauses->sql());
	}

	public function testBuildFromThenGroupBy() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->groupBy(['firstname'])
			->having('firstname > 10')
			->orderBy(['firstname' => 'DESC'])
			->limit(10)
			->offset(100);
		Assert::same('SELECT firstname, lastname FROM person GROUP BY firstname HAVING firstname > 10 ORDER BY firstname DESC LIMIT 10 OFFSET 100', $clauses->sql());
	}

	public function testBuildFromThenHaving() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->having('firstname > 10')
			->orderBy(['firstname' => 'DESC'])
			->limit(10)
			->offset(100);
		Assert::same('SELECT firstname, lastname FROM person HAVING firstname > 10 ORDER BY firstname DESC LIMIT 10 OFFSET 100', $clauses->sql());
	}

	public function testBuildFromThenOrderBy() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->orderBy(['firstname' => 'DESC'])
			->limit(10)
			->offset(100);
		Assert::same('SELECT firstname, lastname FROM person ORDER BY firstname DESC LIMIT 10 OFFSET 100', $clauses->sql());
	}

	public function testBuildFromThenLimit() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->limit(10)
			->offset(100);
		Assert::same('SELECT firstname, lastname FROM person LIMIT 10 OFFSET 100', $clauses->sql());
	}

	public function testBuildFromThenOffset() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->offset(100);
		Assert::same('SELECT firstname, lastname FROM person OFFSET 100', $clauses->sql());
	}

	public function testOffsetThenLimit() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->offset(100)
			->limit(10);
		Assert::same('SELECT firstname, lastname FROM person OFFSET 100 LIMIT 10', $clauses->sql());
	}

	public function testJoin() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->join('INNER', 'others', 'person.firstname = others.firstname');
		Assert::same('SELECT firstname, lastname FROM person INNER JOIN others ON person.firstname = others.firstname', $clauses->sql());
	}

	public function testMultipleJoins() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->join('INNER', 'others', 'person.firstname = others.firstname')
			->join('LEFT', 'others2', 'others.firstname = others2.firstname')
			->where('age > 20');
		Assert::same('SELECT firstname, lastname FROM person INNER JOIN others ON person.firstname = others.firstname LEFT JOIN others2 ON others.firstname = others2.firstname WHERE age > 20', $clauses->sql());
	}

	public function testMultipleAndWhere() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->where('age > 20')
			->andWhere('age > 30')
			->andWhere('age > 40')
			->limit(10);
		Assert::same('SELECT firstname, lastname FROM person WHERE age > 20 AND age > 30 AND age > 40 LIMIT 10', $clauses->sql());
	}

	public function testMultipleOrWhere() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->where('age > 20')
			->orWhere('age > 30')
			->orWhere('age > 40')
			->limit(10);
		Assert::same('SELECT firstname, lastname FROM person WHERE age > 20 OR age > 30 OR age > 40 LIMIT 10', $clauses->sql());
	}

	public function testMultipleOrAndWhere() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->where('age > 20')
			->orWhere('age > 30')
			->andWhere('age > 35')
			->orWhere('age > 40')
			->limit(10);
		Assert::same('SELECT firstname, lastname FROM person WHERE age > 20 OR age > 30 AND age > 35 OR age > 40 LIMIT 10', $clauses->sql());
	}

	public function testMultipleOrder() {
		$clauses = (new Storage\Clauses\AnsiSelect(['firstname', 'lastname']))
			->from(['person'])
			->orderBy(['firstname' => 'ASC', 'lastname' => 'DESC']);
		Assert::same('SELECT firstname, lastname FROM person ORDER BY firstname ASC, lastname DESC', $clauses->sql());
	}
}

(new ClausesBuild())->run();