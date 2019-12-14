<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1.0
 */

namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Tester\Assert;
use Tester\FileMock;

require __DIR__ . '/../bootstrap.php';

final class CachedPDOStatement extends TestCase\PostgresDatabase {
	public function testCachingOnSecondRun() {
		$query = 'SELECT first, second FROM table1';
		$origin = $this->mock(\PDOStatement::class);
		$metaColumn = ['type' => 'integer', 'name' => 'first', 'table' => 'table1'];
		$temp = new \SplFileInfo(FileMock::create());
		$origin->shouldReceive('getColumnMeta')->with('first')->once()->andReturn($metaColumn);
		Assert::same($metaColumn, (new Storage\CachedPDOStatement($origin, $query, $temp))->getColumnMeta(0));
		Assert::same($metaColumn, (new Storage\CachedPDOStatement($origin, $query, $temp))->getColumnMeta(0));
		Assert::same($metaColumn, require sprintf('%s/postgres_column_meta/%s/0.php', $temp->getPathname(), md5($query)));
	}
}

(new CachedPDOStatement())->run();