<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class CachedPDOStatementTest extends TestCase\PostgresDatabase {
	public function testCachingOnSecondRun(): void {
		$query = 'SELECT first, second FROM table1';
		$origin = $this->mock(\PDOStatement::class);
		$metaColumn = ['type' => 'integer', 'name' => 'first', 'table' => 'table1'];
		$temp = new \SplFileInfo(__DIR__ . '/../Temporary');
		$origin->shouldReceive('getColumnMeta')->with('first')->once()->andReturn($metaColumn);
		assert($origin instanceof \PDOStatement);
		Assert::same($metaColumn, (new Storage\CachedPDOStatement($origin, $query, $temp))->getColumnMeta(0));
		Assert::same($metaColumn, (new Storage\CachedPDOStatement($origin, $query, $temp))->getColumnMeta(0));
		Assert::same($metaColumn, require sprintf('%s/postgres_column_meta/%s/0.php', $temp->getPathname(), md5($query)));
	}
}

(new CachedPDOStatementTest())->run();
