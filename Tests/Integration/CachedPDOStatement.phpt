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

require __DIR__ . '/../bootstrap.php';

final class CachedPDOStatement extends TestCase\PostgresDatabase {
	public function testCachingOnSecondRun() {
		$query = 'SELECT first, second FROM table1';
		$origin = $this->mock(\PDOStatement::class);
		$metaColumn = ['type' => 'integer', 'name' => 'first', 'table' => 'table1'];
		$origin->shouldReceive('getColumnMeta')->with('first')->once()->andReturn($metaColumn);
		Assert::same($metaColumn, (new Storage\CachedPDOStatement($origin, $query, $this->redis))->getColumnMeta(0));
		Assert::same($metaColumn, (new Storage\CachedPDOStatement($origin, $query, $this->redis))->getColumnMeta(0));
		Assert::same($metaColumn, (new Storage\StringData())->unserialize($this->redis->hget(sprintf('postgres:column:meta:%s', md5($query)), 0)));
	}
}

(new CachedPDOStatement())->run();